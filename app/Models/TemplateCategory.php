<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateCategory extends Model
{
    /**
     * Get the insurances related to this template category.
     *
     * @return HasMany
     */
    public function insurances()
    {
        return $this->hasMany(Insurance::class);
    }

    /**
     * Get all templates associated with the model.
     *
     * @return HasMany
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    /**
     * Get only the overdue recall templates associated with the model.
     *
     * @return HasMany
     */
    public function overdueTemplates(): HasMany
    {
        $overdueName = config('constants.CAMPAIGN_TYPE.OVERDUE_RECALLS');

        return $this->templates()->whereHas('campaignType', function ($query) use ($overdueName) {
            $query->where('name', $overdueName);
        })
            ->orderByDesc('is_default');
    }
}
