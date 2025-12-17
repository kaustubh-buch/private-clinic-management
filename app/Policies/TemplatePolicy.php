<?php

namespace App\Policies;

use App\Models\Template;
use App\Models\User;

class TemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Template $template): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Template $template): bool
    {
        $clinic = $user->clinics()->first();

        return $clinic->id === $template->clinic_id;
    }

    /**
     * Determine if the given template can be set as default for the user's clinic.
     *
     * @param User     $user     The user attempting to set the template.
     * @param Template $template The template being checked.
     *
     * @return bool True if the template can be set as default, false otherwise.
     */
    public function setDefault(User $user, Template $template): bool
    {
        $clinic = $user->clinics;

        if (! $clinic) {
            return false;
        }

        $isTemplateForClinic = $template->clinic_id && $template->clinic_id == $clinic->id;
        $isAdminTemplate = ! $template->clinic_id;

        return $isTemplateForClinic || $isAdminTemplate;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Template $template): bool
    {
        $clinic = $user->clinics()->first();

        return $clinic->id === $template->clinic_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Template $template): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Template $template): bool
    {
        return false;
    }
}
