<?php

namespace App\Repositories;

use App\Models\Insurance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InsuranceRepository extends CommonRepository
{
    /**
     * Create a new class instance.
     *
     * @param Insurance $model The Insurance model instance.
     */
    public function __construct(Insurance $model)
    {
        parent::__construct($model);
    }

    /**
     * Retrieve an insurance record by abbreviation for admin users.
     *
     * @param string $abbreviation The abbreviation of the insurance.
     *
     * @return Insurance|null The insurance record or null if not found.
     */
    public function getByAbbreviationAdmin(string $abbreviation): ?Insurance
    {
        return $this->model
            ->abbreviation($abbreviation)
            ->forClinic(null)
            ->first();
    }

    /**
     * Retrieve an insurance record by abbreviation for a specific clinic.
     *
     * @param string $abbreviation The abbreviation of the insurance.
     * @param int    $clinic_id    The ID of the clinic.
     *
     * @return Insurance|null The insurance record or null if not found.
     */
    public function getByAbbreviationClinic(string $abbreviation, int $clinic_id): ?Insurance
    {
        return $this->model
            ->abbreviation($abbreviation)
            ->forClinic($clinic_id)
            ->first();
    }

    /**
     * Retrieve pending insurance approvals.
     *
     * This method fetches insurances that are associated with clinics but do not have
     * a corresponding admin copy (abbreviation match with null clinic_id).
     *
     * @return Builder The query builder for pending insurance approvals.
     */
    public function pendingInsuranceApproval(): Builder
    {
        return $this->model->newQueryWithoutScopes()->from('insurances as clinic_insurance')
            ->leftJoin('insurances as admin_insurance', function ($join) {
                $join->on('clinic_insurance.abbreviation', '=', 'admin_insurance.abbreviation')
                    ->whereNull('admin_insurance.clinic_id');
            })
            ->join('clinics as cl', 'clinic_insurance.clinic_id', '=', 'cl.id')
            ->whereNotNull('clinic_insurance.clinic_id')
            ->whereNotNull('clinic_insurance.common_name')
            ->whereNull('admin_insurance.id')
            ->whereNull('clinic_insurance.deleted_at')
            ->whereNull('admin_insurance.deleted_at')
            ->whereNull('cl.deleted_at')
            ->select('clinic_insurance.id', 'clinic_insurance.abbreviation', 'clinic_insurance.common_name', 'cl.name as clinic_name', 'cl.contact_no as contact_no');
    }

    /**
     * Check if an admin copy of the insurance exists based on the abbreviation.
     *
     * @param string $abbreviation The abbreviation to check.
     *
     * @return bool True if an admin copy exists, false otherwise.
     */
    public function isExistAdminCopy(string $abbreviation): bool
    {
        return $this->model->whereNull('clinic_id')
            ->where('abbreviation', $abbreviation)
            ->count() > 0;
    }

    /**
     * Build the query for retrieving other insurances for a specific clinic.
     *
     * @param int $clinicId The ID of the clinic.
     *
     * @return Builder The query builder for other insurances.
     */
    public function buildOtherInsuranceDataTableQuery(int $clinicId): Builder
    {
        return $this->model->where('clinic_id', $clinicId)
            ->where('template_category_id', config('constants.TEMPLATE_CATEGORY.OTHER_INSURANCE.id'))
            ->whereHas('templateCategory');
    }

    /**
     * Build the query for retrieving preferred provider insurances for a specific clinic.
     *
     * @param int $clinicId The ID of the clinic.
     *
     * @return Builder The query builder for preferred provider insurances.
     */
    public function buildPreferredProviderInsuranceDataTableQuery(int $clinicId): Builder
    {
        return $this->model->where('clinic_id', $clinicId)
            ->where('template_category_id', config('constants.TEMPLATE_CATEGORY.PREFERRED_PROVIDER.id'))
            ->whereHas('templateCategory');
    }

    /**
     * Get insurance IDs for a specific clinic and category.
     *
     * @param int $clinic_id   The ID of the clinic.
     * @param int $category_id The ID of the template category.
     *
     * @return Collection The collection of insurance IDs.
     */
    public function getInsuranceIds(int $clinic_id, int $category_id): Collection
    {
        return $this->model->forClinic($clinic_id)
            ->where('template_category_id', $category_id)
            ->pluck('id');
    }

    /**
     * Check if the clinic has any insurance records without a common name.
     *
     * @param int $clinic_id The ID of the clinic to check.
     *
     * @return bool True if there are insurances without a common name, false otherwise.
     */
    public function hasInsurancesWithoutCommonName(int $clinic_id): bool
    {
        return $this->model->forClinic($clinic_id)->whereNull('common_name')->count() > 0;
    }
}
