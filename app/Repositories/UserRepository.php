<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends CommonRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Reset the login attempt count for the given user.
     *
     * @param int $userId
     *
     * @return void
     */
    public function resetLoginAttemptCounts(int $userId)
    {
        $this->model->find($userId)->update(['attempt_count' => 0]);
    }

    /**
     * Reterives user from email ID.
     *
     * @param string $email
     *
     * @return User
     */
    public function getUserByMailId(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Handle a failed login attempt for the given user.
     *
     * @param User $user
     *
     * @return void
     */
    public function handleFailedLoginAttempt(User $user): void
    {
        $attemptCount = $user->attempt_count + 1;
        $maxAttempts = config('auth.login_attempt.max_attempts');
        $lockMinutes = config('auth.login_attempt.lock_minutes');

        $updateData = ['attempt_count' => $attemptCount];

        if ($attemptCount >= $maxAttempts) {
            $updateData = [
                'blocked_until' => now()->addMinutes($lockMinutes),
                'attempt_count' => 0,
            ];
        }

        $this->update($user->id, $updateData);
    }

    /** Retrieve all users who are on the free plan.
     *
     * @return Collection
     */
    public function getUserForTrialEndReminder(): Collection
    {
        return $this->model->whereHas('activeSubscription', function ($q) {
            $q->whereHas('subscriptionPlan', function ($q) {
                $q->where('is_free_plan', 1);
            })->where('subscription_status', 'active')->whereDate('subscription_end_date', Carbon::now()->addDays(3)->startOfDay());
        })->get();
    }

    /**
     * Get all User whose latest subscription invoice has failed payment attempts
     * on the specified target dates.
     *
     * @param array $targetDates Array of date strings (format: Y-m-d) to match against the payment's created_at field.
     *
     * @return Collection Collection of User models with nested relationships loaded.
     */
    public function getFailedSubscriptionPayment(array $targetDates): Collection
    {
        return $this->model->with([
            'latestSubscription.latestSubscriptionBilling.latestInvoice.latestPayment',
        ])->get()->filter(function ($model) use ($targetDates) {
            $invoice = $model->latestSubscription?->latestSubscriptionBilling?->latestInvoice;
            $payment = $invoice?->latestPayment;

            return $invoice && $invoice->status === config('constants.INVOICE_STATUS.FAIL')
                && $payment && $payment->status !== config('constants.STRIPE.PAYMENT_INTENT.SUCCEEDED')
                && in_array($payment->created_at->toDateString(), $targetDates);
        });
    }
}
