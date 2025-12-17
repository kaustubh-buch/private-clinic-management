<?php

namespace App\Observers;

use App\Models\Template;
use App\Services\ActivityService;

class TemplateObserver
{
    /**
     * Constructor to initialize the ActivityService dependency.
     *
     * @param ActivityService $activityService Service for handling user or system activities.
     */
    public function __construct(
        private ActivityService $activityService,
    ) {
    }

    /**
     * Handle the Template "created" event.
     *
     * @param Template $template
     *
     * @return void
     */
    public function created(Template $template): void
    {
        $this->activityService->logActivity($template, [
            'event_display' => config('constants.ACTIVITY_LOG.EVENT_DISPLAY.TEMPLATE_CREATED'),
        ]);
    }

    /**
     * Handle the Template "updated" event.
     *
     * @param Template $template
     *
     * @return void
     */
    public function updated(Template $template): void
    {
        $this->activityService->logActivity($template, [
            'event_display' => config('constants.ACTIVITY_LOG.EVENT_DISPLAY.TEMPLATE_UPDATED'),
        ]);
    }

    /**
     * Handle the Template "deleted" event.
     *
     * @param Template $template
     *
     * @return void
     */
    public function deleted(Template $template): void
    {
        $this->activityService->logActivity($template, [
            'event' => 'delete',
            'event_display' => config('constants.ACTIVITY_LOG.EVENT_DISPLAY.TEMPLATE_DELETED'),
        ]);
    }
}
