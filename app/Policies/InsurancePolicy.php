<?php

namespace App\Policies;

use App\Models\Insurance;
use App\Models\User;

class InsurancePolicy
{
    /**
     * Determine if the given insurance belongs to one of the user's clinics.
     *
     * @param User      $user
     * @param Insurance $insurance
     *
     * @return bool
     */
    public function belongsToClinic(User $user, Insurance $insurance): bool
    {
        return $user->clinics && $insurance->clinic_id === $user->clinics->id;
    }
}
