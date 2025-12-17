<?php

namespace App\Repositories;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClinicRepository extends CommonRepository
{
    /**
     * ClinicRepository constructor.
     *
     * @param Clinic $model
     */
    public function __construct(Clinic $model)
    {
        parent::__construct($model);
    }

    /**
     * Update or create a clinic record associated with a user.
     *
     * @param User  $user
     * @param array $clinicData
     *
     * @return Clinic
     */
    public function updateOrCreateByUser(User $user, array $clinicData): Clinic
    {
        return $user->clinics()->updateOrCreate([], $clinicData);
    }

    /**
     * Get the base query for clinics filtered by the provided tab type.
     *
     * This method builds the query with required relationships and conditions
     * based on the tab name (e.g. pending, free trial, recent, etc).
     *
     * @param string $tab The current tab identifier to filter clinics by status
     *
     * @return Builder
     */
    public function getClinicsQuery(string $tab): Builder
    {
        $today = now()->toDateString();

        $query = $this->model
            ->with(['user', 'state', 'driverLicence', 'software'])
            ->join('users', function ($join) {
                $join->on('clinics.user_id', '=', 'users.id')
                    ->whereNull('users.deleted_at');
            })
            ->leftJoin('states', function ($join) {
                $join->on('states.id', '=', 'clinics.state_id')
                    ->whereNull('states.deleted_at');
            })
            ->leftJoin('software', function ($join) {
                $join->on('clinics.software_id', '=', 'software.id')
                    ->whereNull('software.deleted_at');
            })
            ->leftJoin('user_subscription_plans', function ($join) {
                $join->on('clinics.user_id', '=', 'user_subscription_plans.user_id')
                    ->whereNull('user_subscription_plans.deleted_at');
            })
            ->leftJoin('subscription_plans', function ($join) {
                $join->on('user_subscription_plans.subscription_plan_id', '=', 'subscription_plans.id')
                    ->whereNull('subscription_plans.deleted_at');
            })
            ->select(
                'clinics.*',
                'users.first_name',
                'users.last_name',
                'users.email',
                'states.name as state_name',
                'software.name as software_name'
            )
            ->whereNull('clinics.deleted_at')
            ->distinct();

        switch ($tab) {
            case 'PENDING_APPROVAL':
                $query->where('clinics.status', config('constants.CLINICS.STATUS.PENDING'));
                break;
            case 'DENIED':
                $query->where('clinics.status', config('constants.CLINICS.STATUS.DENIED'));
                break;
            case 'FREE_TRIAL':
                $query->where('clinics.status', config('constants.CLINICS.STATUS.APPROVED'))
                    ->where('subscription_plans.is_free_plan', 1)
                    ->whereDate('user_subscription_plans.subscription_start_date', '<=', $today)
                    ->whereDate('user_subscription_plans.subscription_end_date', '>=', $today);
                break;
            case 'SUBSCRIBED_RECENT':
                $query->where('clinics.status', config('constants.CLINICS.STATUS.APPROVED'))
                    ->where('subscription_plans.is_free_plan', 0)
                    ->whereHas('user.activeSubscriptionStatus')
                    ->whereDate('user_subscription_plans.subscription_start_date', '<=', $today)
                    ->whereDate('user_subscription_plans.subscription_end_date', '>=', $today);

                break;
            case 'CANCELLED_OR_PAUSED':
                $query->where('clinics.status', config('constants.CLINICS.STATUS.APPROVED'))
                    ->where('user_subscription_plans.subscription_status', config('constants.SUBSCRIPTION_PLAN.STATUS.INACTIVE'))
                    ->whereDoesntHave('user.activeSubscriptionStatus');
                break;
        }

        return $query;
    }

    /**
     * Get the billing history IDs associated with a clinic.
     *
     * @param Clinic $clinic
     *
     * @return Collection
     */
    public function getBillingHistoryIdsForClinic(Clinic $clinic)
    {
        return $clinic->user->userSubscriptionPlan()
            ->with('billingHistories')
            ->get()
            ->pluck('billingHistories')
            ->flatten()
            ->pluck('id');
    }

    /**
     * Get clinics whose latest data import was exactly 14 days ago,
     * used to notify them for syncing their patient data.
     *
     * @param int $daysAgo
     *
     * @return Collection
     */
    public function notifyClinicsForDataSync(int $daysAgo)
    {
        return $this->model
            ->whereHas('latestImport', function ($query) use ($daysAgo) {
                $query->whereDate(
                    'created_at',
                    now()->subDays($daysAgo)
                );
            })
            ->get();
    }

    /**
     * Get clinics that have not sent any campaigns within their current billing cycle,
     * specifically when it's the 14th day of that billing cycle.
     *
     * @param int $daysAgo
     *
     * @return Collection
     */
    public function getClinicsWithNoCampaignsOnBillingDay(int $daysAgo): Collection
    {
        $date = now()->subDays($daysAgo)->toDateString();

        return $this->model
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'clinics.user_id')
                    ->whereNull('users.deleted_at');
            })
            ->join('user_subscription_plans as usp', function ($join) {
                $join->on('usp.user_id', '=', 'users.id')
                    ->whereNull('usp.deleted_at');
            })
            ->join('subscription_billing_histories as sbh', function ($join) {
                $join->on('sbh.user_subscription_plan_id', '=', 'usp.id')
                    ->whereNull('sbh.deleted_at');
            })
            ->leftJoin('campaigns', function ($join) {
                $join->on('campaigns.clinic_id', '=', 'clinics.id')
                    ->whereBetween('campaigns.created_at', [DB::raw('sbh.cycle_start_date'), DB::raw('sbh.cycle_end_date')])
                    ->whereNull('campaigns.deleted_at');
            })
            ->whereNull('clinics.deleted_at')
            ->whereNull('campaigns.id')
            ->whereDate('sbh.cycle_start_date', '=', $date)
            ->select('clinics.*', 'sbh.total_segments')
            ->distinct()
            ->get();
    }
}
