<?php

namespace App\Repositories;

use App\Models\TemplateCategory;
use Illuminate\Database\Eloquent\Collection;

class TemplateCategoryRepository extends CommonRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct(TemplateCategory $model)
    {
        parent::__construct($model);
    }

    /**
     * Get overdue templates for a specific clinic.
     *
     * @param int $clinic_id
     *
     * @return Collection
     */
    public function getOverdueTemplates($clinic_id): Collection
    {
        return $this->model::with(['overdueTemplates' => function ($query) use ($clinic_id) {
            $query->where('clinic_id', $clinic_id)
                ->orWhereNull('clinic_id');
        }])->get();
    }

    /**
     * Get the "Other Insurance" record.
     *
     * @return TemplateCategory|null
     */
    public function otherInsurance()
    {
        return $this->find(config('constants.TEMPLATE_CATEGORY.OTHER_INSURANCE.id'));
    }

    /**
     * Get the "Preferred Provider" record.
     *
     * @return \App\Models\Insurance|null
     */
    public function preferedProvider()
    {
        return $this->find(config('constants.TEMPLATE_CATEGORY.PREFERRED_PROVIDER.id'));
    }
}
