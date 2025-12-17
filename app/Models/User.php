<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'attempt_count',
        'blocked_until',
        'email_verified_at',
        'company_name',
        'company_abn',
        'first_name',
        'last_name',
        'last_verification_sent_at',
        'dob',
        'receives_promotional_emails',
        'is_fully_registered',
        'last_logged_in_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if the user is blocked or not.
     *
     * @return bool
     */
    public function getIsBlockedAttribute(): bool
    {
        return $this->blocked_until !== null && now() < $this->blocked_until;
    }

    /**
     * Get the subscription plan associated with the user.
     *
     * @return HasMany
     */
    public function userSubscriptionPlan(): HasMany
    {
        return $this->hasMany(UserSubscriptionPlan::class);
    }

    /**
     * Get the driver's license associated with the user.
     *
     * @return HasOne
     */
    public function driverLicence(): HasOne
    {
        return $this->hasOne(DriverLicence::class);
    }

    /**
     * Get the clinic associated with the user.
     *
     * @return HasOne
     */
    public function clinics(): HasOne
    {
        return $this->hasOne(Clinic::class);
    }

    /**
     * Get the payment methods associated with the user.
     *
     * @return HasMany
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Define a one-to-one relationship with UserSubscriptionPlan model.
     *
     * @return HasOne
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(UserSubscriptionPlan::class)->activeSubscription()->latest();
    }

    /**
     * Define a one-to-one relationship with UserSubscriptionPlan model.
     *
     * @return HasOne
     */
    public function activeSubscriptionStatus(): HasOne
    {
        return $this->hasOne(UserSubscriptionPlan::class)->activeSubscriptionStatus()->latest();
    }

    /**
     * Get the driver's licenses associated with the user.
     *
     * @return HasOne
     */
    public function driverLicenses()
    {
        return $this->hasOne(DriverLicence::class);
    }

    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Get the count of unread messages for the user's clinic.
     *
     * @return int
     */
    public function unreadMessagesCount()
    {
        return ! empty($this->clinics)
            ? $this->clinics?->messages()
                ->unread()
                ->count()
            : 0;
    }

    /**
     * Check if the user requires two-factor authentication.
     *
     * @return bool
     */
    public function requires2FA()
    {
        if ($this->hasRole(config('constants.GLOBAL.ROLES.ADMIN'))) {
            return ! empty($this->phone_number) && ! empty($this->country_code) && $this->country_code == config('constants.GLOBAL.DEFAULT_COUNTRY_CODE');
        }

        return ! empty($this->clinics) && ! empty($this->clinics->mobile_no);
    }

    /**
     * Get the default payment method for the user.
     *
     * @return HasOne
     */
    public function defaultPaymentMethod(): HasOne
    {
        return $this->hasOne(PaymentMethod::class)
            ->where('is_default_payment_id', true);
    }

    /** Define a relationship to retrieve the latest subscription plan for the user.
     *
     * @return HasOne
     */
    public function latestSubscription(): HasOne
    {
        return $this->hasOne(UserSubscriptionPlan::class)
            ->where('subscription_start_date', '<=', now())
            ->latest('id');
    }

    /**
     * Get all invoices related to this subscription billing history.
     *
     * @return HasMany
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /** Check if user has failed payments for active subscription.
     *
     * @return bool
     */
    public function hasFailedSubscriptionPayment(): bool
    {
        return $this->latestSubscription ? $this->latestSubscription->hasFailedSubscriptionPayment() : false;
    }

    /** Check if user has subscription payment pending.
     *
     * @return bool
     */
    public function hasSubscriptionPaymentPending(): bool
    {
        return $this->latestSubscription ? $this->latestSubscription->subscription_status == config('constants.SUBSCRIPTION_PLAN.STATUS.PAYMENT_PENDING') : false;
    }

    /**
     * Determine if the current subscription is a free trial and today is its last day.
     *
     * @return bool
     */
    public function isFreeTrialLastDay(): bool
    {
        return $this->activeSubscription
            && $this->activeSubscription->isFreePlan()
            && $this->activeSubscription->subscription_end_date->startOfDay()->diffInDays(Carbon::now()->startOfDay()) == 0;
    }

    /**
     * Function used to get upcoming subscription of the user.
     *
     * @return mixed
     */
    public function getUpcomingSubscriptions()
    {
        return $this->userSubscriptionPlan
            ->whereNull('cancelled_at')
            ->where('subscription_status', config('constants.SUBSCRIPTION_PLAN.STATUS.PAYMENT_PENDING'))
            ->where('subscription_start_date', '>', Carbon::today())
            ->first();
    }

    /**
     * Get the second last (previous) subscription plan.
     *
     * @return UserSubscriptionPlan|null
     */
    public function secondLastSubscriptionPlan()
    {
        return $this->userSubscriptionPlan()
            ->orderBy('id', 'desc') // or 'id' if more appropriate
            ->skip(1)
            ->take(1)
            ->first();
    }

    /**
     * Determine whether the user is allowed to send a message.
     *
     * Conditions:
     * - User must have an active or non-expired subscription.
     * - If the last payment failed:
     *   - Deny if it's been 6 or more days since the last failed payment.
     *   - Deny if no additional segments are available in the latest billing.
     *
     * @return bool True if the user is allowed to send messages, false otherwise.
     */
    public function canSendMessage(): bool
    {
        $latestSubscription = $this->latestSubscription;

        if (! $latestSubscription) {
            return false;
        }

        if ($this->hasFailedSubscriptionPayment()) {
            return $this->canSendAfterFailedPayment($latestSubscription);
        }

        $latestBilling = $latestSubscription->latestSubscriptionBilling;
        $notExceedSegments = $latestBilling && $latestBilling->additional_segment_count <= $latestBilling->total_additional_segments;

        return ! $latestSubscription->isExpired() && $notExceedSegments;
    }

    /**
     * Determine if the user can send a message after a failed payment.
     *
     * @param UserSubscriptionPlan $subscription
     *
     * @return bool
     */
    private function canSendAfterFailedPayment(UserSubscriptionPlan $subscription): bool
    {
        if ($subscription->getDaysSinceLastFailedPayment() >= config('constants.PAYMENT_FAILED_RESTRICT_AFTER_DAYS')) {
            return false;
        }

        $latestBilling = $subscription->latestSubscriptionBilling;

        return $latestBilling && $latestBilling->total_segments > $latestBilling->total_free_segments && $latestBilling->additional_segment_count == 0;
    }

    /**
     * Get the user's relevant timezone (clinic's state timezone for doctors, app default otherwise).
     *
     * @return string
     */
    public function getTimezone(): string
    {
        if ($this->hasRole(config('constants.GLOBAL.ROLES.DOCTOR'))) {
            return $this->clinics?->state->timezone ?? config('app.timezone');
        }

        return config('app.timezone');
    }

    /**
     * Determine if the user has converted to a paid subscription after the free trial.
     *
     * @return bool
     */
    public function getConvertedAfterTrialAttribute(): bool
    {
        return $this->userSubscriptionPlan()
            ->whereHas('subscriptionPlan', function ($query) {
                $query->where('is_free_plan', 0);
            })
            ->exists();
    }

    /**
     * Get the maximum allowed date based on subscription status.
     *
     * @return Carbon|null
     */
    public function getMaxAllowedDate(): ?Carbon
    {
        $activeSubscription = $this->activeSubscription;

        if (! $activeSubscription) {
            return $this->latestSubscription->getFailedPaymentEndDate() ?? null;
        }

        if ($activeSubscription->isFreePlan()) {
            return Carbon::parse($activeSubscription->subscription_end_date);
        }

        if (! empty($activeSubscription->cancelled_at)) {
            return Carbon::parse($activeSubscription->cancelled_at)->startOfDay()->addDays(config('constants.CANCEL_GRACE_DAYS'));
        }

        return Carbon::parse($activeSubscription->subscription_end_date)->startOfDay()->addDays(config('constants.SUBSCRIPTION_EXTENSION_DAYS'));
    }

    /**
     * Check if the user has limited access due to failed subscription payment
     *
     *
     * @return bool
     */
    public function hasLimitedAccessDueToFailedPayment(): bool
    {
        $activeSubscription =  $this->latestSubscription;

        if ($activeSubscription && $activeSubscription->getDaysSinceLastFailedPayment() >= config('constants.PAYMENT_FAILED_RESTRICT_AFTER_DAYS')) {
            return true;
        }

        return false;
    }

    /**
     * Check if the user has limited access due to pending payment.
     *
     *
     * @return bool
     */
    public function hasLimitedAccessDueToPendingPayment(): bool
    {
        $activeSubscription =  $this->latestSubscription;

        if ($activeSubscription && $activeSubscription->getDaysSinceLastPendingPayment() >= config('constants.PAYMENT_FAILED_RESTRICT_AFTER_DAYS')) {
            return true;
        }

        return false;
    }
}
