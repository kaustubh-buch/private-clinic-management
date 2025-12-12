<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\CommonRepository;

class UserRepository extends CommonRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Reset the login attempt count for the given user.
     *
     * @param string $userId
     *
     * @return void
     */
    public function resetLoginAttemptCounts(string $userId)
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
}
