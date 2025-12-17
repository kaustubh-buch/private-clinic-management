<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insurance extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'abbreviation',
        'common_name',
        'status',
        'clinic_id',
        'software_id',
        'template_category_id',
        'admin_status',
    ];

    /**
     * Get the patients associated with this insurance.
     *
     * @return HasMany
     */
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Get the template category that owns the template.
     *
     * @return BelongsTo
     */
    public function templateCategory()
    {
        return $this->belongsTo(TemplateCategory::class);
    }

    /**
     * Get the clinic that owns the template.
     *
     * @return BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the software that owns the template.
     *
     * @return BelongsTo
     */
    public function software()
    {
        return $this->belongsTo(Software::class);
    }

    /**
     * Scope a query to only include insurances with the given abbreviation.
     *
     * @param Builder $query
     * @param string  $abbreviation
     *
     * @return Builder
     */
    public function scopeAbbreviation(Builder $query, string $abbreviation): Builder
    {
        return $query->where('abbreviation', $abbreviation);
    }

    /**
     * Scope a query to filter by clinic ID.
     *
     * Pass null to filter insurances without a clinic (admin/global insurances).
     *
     * @param Builder  $query
     * @param int|null $clinic_id
     *
     * @return Builder
     */
    public function scopeForClinic(Builder $query, ?int $clinic_id): Builder
    {
        if (is_null($clinic_id)) {
            return $query->whereNull('clinic_id');
        }

        return $query->where('clinic_id', $clinic_id);
    }

    /**
     * Get the display name for the insurance.
     * Returns the common name if available, otherwise falls back to the abbreviation.
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->common_name ?: $this->abbreviation;
    }

    /**
     * Get the resolved template category ID based on insurance abbreviation and category.
     *
     * @return int
     */
    public function getResolvedTemplateCategoryIdAttribute(): int
    {
        $cdbs = config('constants.SPECIAL_INSURANCE.CDBS');
        $dva = config('constants.SPECIAL_INSURANCE.DVA');

        $abbreviation = $this->abbreviation;
        $templateCategoryId = $this->templateCategory?->id;

        return match (true) {
            $abbreviation === $cdbs => config('constants.TEMPLATE_CATEGORY.CDBC.id'),
            $abbreviation === $dva => config('constants.TEMPLATE_CATEGORY.DVA.id'),
            $templateCategoryId === config('constants.TEMPLATE_CATEGORY.PREFERRED_PROVIDER.id') &&
                ! in_array($abbreviation, [$cdbs, $dva]) => config('constants.TEMPLATE_CATEGORY.PREFERRED_PROVIDER.id'),
            $templateCategoryId === config('constants.TEMPLATE_CATEGORY.OTHER_INSURANCE.id') => config('constants.TEMPLATE_CATEGORY.OTHER_INSURANCE.id'),
            default => config('constants.TEMPLATE_CATEGORY.NO_INSURANCE.id'),
        };
    }
}
