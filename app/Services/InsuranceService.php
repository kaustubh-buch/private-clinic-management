<?php

namespace App\Services;

use App\Models\Insurance;
use App\Repositories\InsuranceRepository;
use App\Repositories\TemplateCategoryRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class InsuranceService
{
    /**
     * Create a new class instance.
     *
     * @param InsuranceRepository        $insuranceRepository
     * @param TemplateCategoryRepository $templateCategoryRepository
     */
    public function __construct(
        protected InsuranceRepository $insuranceRepository,
        protected TemplateCategoryRepository $templateCategoryRepository
    ) {
    }

    /**
     * Create an admin copy of a clinic insurance record.
     *
     * This method finds a clinic insurance record by its ID, checks if an admin copy
     * with the same abbreviation already exists, and creates a new admin copy if not.
     *
     * @param string $commonName
     * @param int    $id         The ID of the clinic insurance record.
     *
     * @throws ModelNotFoundException If the clinic insurance record is not found.
     * @throws ValidationException    If the abbreviation already exists in admin records.
     *
     * @return Insurance The newly created admin insurance record.
     */
    public function createInsuranceCopyForAdmin(string $commonName, int $id): Insurance
    {
        // 1. Find clinic insurance
        $clinicInsurance = $this->insuranceRepository->find($id);

        if (! $clinicInsurance || $clinicInsurance->clinic_id === null) {
            throw new ModelNotFoundException('Clinic insurance record not found.');
        }

        // 2. Check if abbreviation already exists for admin
        $exists = $this->insuranceRepository->isExistAdminCopy($clinicInsurance->abbreviation);

        if ($exists) {
            throw ValidationException::withMessages([
                'abbreviation' => 'This abbreviation already exists in admin records.',
            ]);
        }

        // 3. Create admin copy
        return $this->insuranceRepository->store([
            'abbreviation'           => $clinicInsurance->abbreviation,
            'common_name'            => $commonName,
            'status'                 => config('constants.INSURANCE.STATUS.APPROVED'),
            'admin_status'           => config('constants.INSURANCE.STATUS.APPROVED'),
            'clinic_id'              => null,
            'template_category_id'   => $clinicInsurance->template_category_id,
            'software_id'            => $clinicInsurance->software_id,
        ]);
    }

    /**
     * Move an insurance to a different category (preferred or other).
     *
     * @param int    $id
     * @param string $type
     *
     * @return Model|null
     */
    public function moveCategory(int $id, string $type)
    {
        try {
            $insurance = $this->insuranceRepository->findOrFail($id);
            if (Gate::denies('belongsToClinic', $insurance)) {
                throw new AuthorizationException(__('messages.alerts.unauthorized_action'));
            }

            $templateCategory = $type === 'other'
                ? $this->templateCategoryRepository->preferedProvider()
                : $this->templateCategoryRepository->otherInsurance();

            return $this->insuranceRepository->update($id, [
                'template_category_id' => $templateCategory->id,
            ]);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException(__('messages.message.insurance_not_found'));
        } catch (AuthorizationException $e) {
            throw $e;
        }
    }
}
