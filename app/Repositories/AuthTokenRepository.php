<?php

namespace App\Repositories;

use App\Models\AuthToken;
use App\Models\User;

class AuthTokenRepository extends CommonRepository
{
    /**
     * Create a new instance of AuthTokenRepository.
     *
     * @param AuthToken $model
     *
     * @return void
     */
    public function __construct(AuthToken $model)
    {
        parent::__construct($model);
    }

    /**
     * Check if a token exists for a user within the valid timeframe.
     *
     * @param User   $user
     * @param string $tokenHash The hashed token to check.
     *
     * @return bool True if the token exists, false otherwise.
     */
    public function tokenExistsForUser(User $user, string $tokenHash): bool
    {
        $timezone = $user->getTimezone();

        $nowInLocal = now($timezone);
        $cutoff = $nowInLocal->copy()->setTime(6, 0);

        // If current local time is before 6 AM, go back to yesterday 6 AM
        if ($nowInLocal->lt($cutoff)) {
            $cutoff->subDay();
        }

        $cutoffUTC = $cutoff->timezone('UTC');

        return $this->model->where('user_id', $user->id)
            ->where('token_type', 'two_factor_authentication')
            ->where('token', $tokenHash)
            ->where('created_at', '>=', $cutoffUTC)
            ->exists();
    }
}
