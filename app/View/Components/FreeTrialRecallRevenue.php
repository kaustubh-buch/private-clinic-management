<?php

namespace App\View\Components;

use App\Repositories\RecallHistoryRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class FreeTrialRecallRevenue extends Component
{
    public float $last30Days;

    public float $projected;

    /**
     * Create a new component instance.
     *
     * @param RecallHistoryRepository $recallHistoryRepository
     */
    public function __construct(protected RecallHistoryRepository $recallHistoryRepository)
    {
        $clinicId = optional(Auth::user())->clinic_id;

        if ($clinicId) {
            $this->last30Days = $recallHistoryRepository->getLast30DaysRevenue($clinicId);
            $this->projected = $recallHistoryRepository->getProjectedAdditionalRevenue($clinicId);
        } else {
            $this->last30Days = $this->projected = 0;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.free-trial-recall-revenue');
    }
}
