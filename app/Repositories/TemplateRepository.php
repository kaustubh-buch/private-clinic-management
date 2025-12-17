<?php

namespace App\Repositories;

use App\Models\Template;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TemplateRepository extends CommonRepository
{
    public function __construct(Template $model)
    {
        parent::__construct($model);
    }

    /**
     * Return all “Promotional” templates for a specific clinic.
     *
     * @param int $clinic_id
     *
     * @return Collection
     */
    public function getPromotionalTemplates(int $clinic_id): Collection
    {
        return $this->model
            ->whereHas('campaignType', function ($query) {
                $query->where('name', config('constants.CAMPAIGN_TYPE.PROMOTIONAL'));
            })
            ->where('clinic_id', $clinic_id)
            ->get();
    }

    /**
     * Mark a template as the default for its clinic & category.
     * Any existing default in that clinic/category is unset.
     *
     * @param int $template_id
     * @param int $clinic_id
     * @param int $category_id
     *
     * @return Template
     */
    public function setDefault(
        int $template_id,
        int $clinic_id,
        int $category_id
    ): Template {
        return DB::transaction(function () use ($template_id, $clinic_id, $category_id) {
            $this->model
                ->where('clinic_id', $clinic_id)
                ->where('template_category_id', $category_id)
                ->where('is_default', 1)
                ->update(['is_default' => 0]);

            $template = $this->model
                ->where('id', $template_id)
                ->where(function ($query) use ($clinic_id) {
                    $query->whereNull('clinic_id')
                        ->orWhere('clinic_id', $clinic_id);
                })
                ->firstOrFail();

            if (! $template) {
                throw new ModelNotFoundException('Template not found.');
            }
            $template->is_default = 1;
            $template->save();

            return $template;
        });
    }

    /**
     * Retrieve all overdue templates for a specific clinic and category.
     *
     * @param int $clinic_id   The ID of the clinic to filter by.
     * @param int $category_id The ID of the template category to filter by.
     *
     * @return Collection
     */
    public function getOverdueTemplatesByCategory($clinic_id, $category_id): Collection
    {
        return $this->model::where('clinic_id', $clinic_id)->where('template_category_id', $category_id)
            ->get();
    }

    /**
     * Delete a record by its ID and clinic ID.
     *
     * @param int $id        The ID of the record to delete.
     * @param int $clinic_id The clinic ID for ownership validation.
     *
     * @return bool True if the record was deleted, false if not found.
     */
    public function destroyTemplate($id, $clinic_id)
    {
        $record = $this->model->where('id', $id)->where('clinic_id', $clinic_id)->first();

        if ($record) {
            $record->delete();

            return true;
        }

        return false;
    }

    /**
     * Get the default template for a given category and clinic.
     *
     * @param int $templateCategoryId The ID of the template category.
     * @param int $clinicId           The ID of the clinic.
     *
     * @return Template|null The matching default template or null if none found.
     */
    public function getDefaultTemplateByCategory(int $templateCategoryId, int $clinicId): ?Template
    {
        return $this->model->where('template_category_id', $templateCategoryId)
            ->where('is_default', true)
            ->where(function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId)
                    ->orWhereNull('clinic_id');
            })
            ->orderByRaw('clinic_id IS NULL') // prefer clinic-specific over admin
            ->first();
    }

    /**
     * Retrieve overdue templates by category IDs for a specific clinic.
     *
     * @param int   $clinic_id   The ID of the clinic.
     * @param array $categoryIds An array of template category IDs to filter by.
     *
     * @return Collection The collection of overdue templates matching the criteria.
     */
    public function getOverdueTemplateByCategoryIds(int $clinic_id, array $categoryIds): Collection
    {
        $query = $this->model->where('clinic_id', $clinic_id)->whereHas('campaignType', function ($query) {
            $query->where('name', config('constants.CAMPAIGN_TYPE.OVERDUE_RECALLS'));
        });

        if (! empty($categoryIds)) {
            $query->whereIn('template_category_id', $categoryIds);
        }

        return $query->get();
    }
}
