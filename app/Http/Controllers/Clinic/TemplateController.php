<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TemplateCreateRequest;
use App\Repositories\TemplateCategoryRepository;
use App\Repositories\TemplateRepository;
use Auth;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TemplateController extends Controller
{
    private $templateRepository;

    private $clinic;

    private int $clinic_id;

    private TemplateCategoryRepository $templateCategoryRepository;

    /**
     * Constructor to initialize the template and category repositories,
     * and set the clinic and clinic ID for the authenticated user.
     *
     * @param TemplateRepository         $templateRepository         Repository handling template data operations.
     * @param TemplateCategoryRepository $templateCategoryRepository Repository handling template category operations.
     */
    public function __construct(
        TemplateRepository $templateRepository,
        TemplateCategoryRepository $templateCategoryRepository
    ) {
        $this->templateRepository = $templateRepository;
        $this->templateCategoryRepository = $templateCategoryRepository;
        $this->clinic = Auth::user()->clinics()->first();
        $this->clinic_id = $this->clinic->id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $selectedCampaignTypeId = session('selected_campaign_type_id');
        $isFirstTabActive = is_null($selectedCampaignTypeId) || $selectedCampaignTypeId == 1;
        $selectedTemplateCategoryId = session('selected_template_category_id');

        $templateCategories = $this->templateCategoryRepository->getOverdueTemplates($this->clinic_id);
        $promotionalTemplates = $this->templateRepository->getPromotionalTemplates($this->clinic_id);
        $clinic = Auth::user()->clinics;

        return view('clinic.pages.template.index', compact(
            'templateCategories',
            'promotionalTemplates',
            'selectedCampaignTypeId',
            'isFirstTabActive',
            'selectedTemplateCategoryId',
            'clinic'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TemplateCreateRequest $request
     *
     * @return RedirectResponse
     */
    public function store(TemplateCreateRequest $request): RedirectResponse
    {
        $data = $request->only([
            'name',
            'message',
            'template_category_id',
            'campaign_type_id',
            'category_name',
        ]);
        $data['clinic_id'] = $this->clinic_id;
        if ($data['template_category_id'] != null) {
            $templateData = $this->templateRepository->getOverdueTemplatesByCategory(
                $this->clinic_id,
                $data['template_category_id']
            );

            $templateCount = count($templateData);

            if ($templateCount == config('constants.OVERDUE_TEMPLATE_LIMIT')) {
                return redirect()->route('template.index')->with('error', __('messages.message.overdue_max_limit'));
            }
        } else {
            $promotionalTemplateData = $this->templateRepository->getPromotionalTemplates(
                $this->clinic_id
            );
            $promotionalTemplateCount = count($promotionalTemplateData);

            if ($promotionalTemplateCount == config('constants.PROMOTIONAL_TEMPLATE_LIMIT')) {
                return redirect()->route('template.index')->with('error', __('messages.message.promotional_max_limit'));
            }
        }
        $template = $this->templateRepository->store($data);
        session()->flash('selected_template_category_id', $template->template_category_id);
        session()->flash('selected_campaign_type_id', $template->campaign_type_id);

        return redirect()->route('template.index')->with('success', __('messages.message.template_created'));
    }

    /**
     * Update the specified template in storage for the current clinic.
     *
     * @param TemplateCreateRequest $request
     *
     * @return RedirectResponse
     */
    public function update(TemplateCreateRequest $request): RedirectResponse
    {
        $template = $this->templateRepository->find($request->id);

        if (! $template) {
            abort(Response::HTTP_NOT_FOUND);
        }

        Gate::authorize('update', $template);

        $data = $request->only([
            'name',
            'message',
            'template_category_id',
            'campaign_type_id',
            'category_name',
            'id',
        ]);
        $data['clinic_id'] = $this->clinic_id;
        $template = $this->templateRepository->update($request->id, $data);
        session()->flash('selected_template_category_id', $template->template_category_id);
        session()->flash('selected_campaign_type_id', $template->campaign_type_id);

        return redirect()->route('template.index')->with('success', __('messages.message.template_updated'));
    }

    /**
     * Set a template as default for a given category and authenticated clinic.
     *
     * @param int $id          The ID of the template to be set as default.
     * @param int $category_id The ID of the template category.
     *
     * @return RedirectResponse
     */
    public function setDefault(int $id, int $category_id): RedirectResponse
    {
        try {
            $template = $this->templateRepository->find($id);
            $this->authorize('setDefault', $template);
            $template = $this->templateRepository->setDefault($id, $this->clinic_id, $category_id);
            session()->flash('selected_template_category_id', $template->template_category_id);
            session()->flash('selected_campaign_type_id', $template->campaign_type_id);

            return redirect()->route('template.index')->with('success', __('messages.message.template_set_default'));
        } catch (Exception $e) {
            return redirect()->route('template.index')->with('error', __('messages.global.something_went_wrong'));
        }
    }

    /**
     * Delete the specified template by ID.
     *
     * @param string $id The ID of the template to delete.
     *
     * @return RedirectResponse Redirects back to the template index with a success message.
     */
    public function destroy(string $id): RedirectResponse
    {
        $template = $this->templateRepository->find($id);

        session()->flash('selected_template_category_id', $template->template_category_id);
        session()->flash('selected_campaign_type_id', $template->campaign_type_id);

        if (! $template) {
            abort(Response::HTTP_NOT_FOUND);
        }

        Gate::authorize('delete', $template);

        $deleted = $this->templateRepository->destroy($id);

        if (! $deleted) {
            return redirect()->route('template.index')
                ->with('error', __('messages.validation.template_not_found_or_unauthorized'));
        }

        return redirect()->route('template.index')->with('success', __('messages.message.template_deleted'));
    }
}
