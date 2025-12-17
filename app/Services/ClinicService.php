<?php

namespace App\Services;

use App\Events\SubscriptionUpgradeCheckRequested;
use App\Helpers\CommonHelper;
use App\Http\Requests\Admin\ApproveClinicRequest;
use App\Http\Requests\Admin\DenyClinicRequest;
use App\Mail\FreeTrialStartMail;
use App\Mail\SuspendMail;
use App\Models\Clinic;
use App\Models\User;
use App\Repositories\CampaignRepository;
use App\Repositories\ClinicRepository;
use App\Repositories\DrivingLicenseRepository;
use App\Repositories\ImportRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\MessageRepository;
use App\Repositories\PatientRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\RecallHistoryRepository;
use App\Repositories\SubscriptionBillingHistoryRepository;
use App\Repositories\SubscriptionPlanRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserSubscriptionPlanRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ClinicService
{
    protected $clinicRepository;

    protected $invoiceRepository;

    protected $userSubscriptionPlanRepository;

    protected $subscriptionBillingHistoryRepository;

    protected $messageRepository;

    protected $patientRepository;

    protected $importRepository;

    protected $paymentMethodRepository;

    protected $userRepository;

    protected $subscriptionPlanRepository;

    protected $subscriptionService;

    protected $campaignRepository;

    protected $recallHistoryRepository;

    protected $fileUploadService;

    protected $drivingLicenseRepository;

    protected $noteService;

    /**
     * Create a new class instance.
     *
     * @param ClinicRepository                     $clinicRepository
     * @param InvoiceRepository                    $invoiceRepository
     * @param UserSubscriptionPlanRepository       $userSubscriptionPlanRepository
     * @param SubscriptionBillingHistoryRepository $subscriptionBillingHistoryRepository
     * @param MessageRepository                    $messageRepository
     * @param PatientRepository                    $patientRepository
     * @param ImportRepository                     $importRepository
     * @param PaymentMethodRepository              $paymentMethodRepository
     * @param SubscriptionPlanRepository           $subscriptionPlanRepository
     * @param UserRepository                       $userRepository
     * @param SubscriptionService                  $subscriptionService
     * @param CampaignRepository                   $campaignRepository
     * @param RecallHistoryRepository              $recallHistoryRepository
     * @param FileUploadService                    $fileUploadService
     * @param DrivingLicenseRepository             $drivingLicenseRepository
     * @param NoteService                          $noteService
     */
    public function __construct(
        ClinicRepository $clinicRepository,
        InvoiceRepository $invoiceRepository,
        UserSubscriptionPlanRepository $userSubscriptionPlanRepository,
        SubscriptionBillingHistoryRepository $subscriptionBillingHistoryRepository,
        MessageRepository $messageRepository,
        PatientRepository $patientRepository,
        ImportRepository $importRepository,
        PaymentMethodRepository $paymentMethodRepository,
        SubscriptionPlanRepository $subscriptionPlanRepository,
        UserRepository $userRepository,
        SubscriptionService $subscriptionService,
        CampaignRepository $campaignRepository,
        RecallHistoryRepository $recallHistoryRepository,
        FileUploadService $fileUploadService,
        DrivingLicenseRepository $drivingLicenseRepository,
        NoteService $noteService
    ) {
        $this->clinicRepository = $clinicRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->userSubscriptionPlanRepository = $userSubscriptionPlanRepository;
        $this->subscriptionBillingHistoryRepository = $subscriptionBillingHistoryRepository;
        $this->messageRepository = $messageRepository;
        $this->patientRepository = $patientRepository;
        $this->importRepository = $importRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->userRepository = $userRepository;
        $this->subscriptionPlanRepository = $subscriptionPlanRepository;
        $this->subscriptionService = $subscriptionService;
        $this->campaignRepository = $campaignRepository;
        $this->recallHistoryRepository = $recallHistoryRepository;
        $this->fileUploadService = $fileUploadService;
        $this->drivingLicenseRepository = $drivingLicenseRepository;
        $this->noteService = $noteService;
    }

    /**
     * Approve a clinic by setting its 'is_approved' status to true.
     *
     * @param ApproveClinicRequest $request
     * @param int                  $id      The ID of the clinic to approve.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the clinic is not found.
     *
     * @return Clinic The updated clinic instance after approval.
     */
    public function approveClinic(ApproveClinicRequest $request, int $id)
    {
        $clinic = $this->clinicRepository->findOrFail($id);

        $this->clinicRepository->update(
            $clinic->id,
            [
                'status' => config('constants.CLINICS.STATUS.APPROVED'),
                'dedicated_number' => $request->dedicated_number,
                'dedicated_country_code' => $request->dedicated_country_code,
                'approved_at' => now(),
            ]
        );

        $this->deleteDrivingLicenseForUser($clinic->user_id);

        if (! empty($request->note)) {
            $this->noteService->addClinicNote($clinic, $request->note, config('constants.NOTE_ACTIVITY.APPROVE'));
        }

        $freePlan = $this->subscriptionPlanRepository->getFreePlan();

        if ($freePlan) {
            $this->subscriptionService->createFreePlanForUser(
                $clinic->user_id,
                $freePlan
            );

            event(new SubscriptionUpgradeCheckRequested($clinic));
            Mail::to($clinic->user->email)->send(new FreeTrialStartMail($clinic));
        }

        return $clinic;
    }

    /**
     * Deny a clinic by setting its 'is_approved' status to false and saving the disapproval reason.
     *
     * @param int               $id      The ID of the clinic to deny.
     * @param DenyClinicRequest $request
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the clinic is not found.
     *
     * @return Clinic The updated clinic instance after denial.
     */
    public function denyClinic(int $id, DenyClinicRequest $request)
    {
        $user = Auth::user();

        $clinic = $this->clinicRepository->findOrFail($id);
        $update_data = [
            'status' => config('constants.CLINICS.STATUS.DENIED'),
            'disapproval_reason' => $request->reason,
            'disapproved_by' => $user->id,
            'disapproved_at' => now(),
        ];
        $this->clinicRepository->update($clinic->id, $update_data);

        if (! empty($request->note)) {
            $this->noteService->addClinicNote($clinic, $request->note, config('constants.NOTE_ACTIVITY.DENY'));
        }

        $this->deleteDrivingLicenseForUser($clinic->user_id);

        if ($request->reason == config('constants.CLINICS.DENY_REASON.REUPLOAD_DOCUMENTS.VALUE')) {
            $this->drivingLicenseRepository->deleteDrivingLicenseForUser($clinic->user_id);
            $this->userRepository->update($clinic->user_id, [
                'is_fully_registered' => 0,
            ]);
        }

        return $clinic;
    }

    /**
     * Deletes the driving license directory for a specific clinic user.
     *
     * @param int $userId The ID of the user whose driving license folder should be deleted.
     *
     * @return bool Returns true if the directory was successfully deleted, false otherwise.
     */
    public function deleteDrivingLicenseForUser(int $userId): bool
    {
        return $this->fileUploadService->deleteDirectory(config('constants.GLOBAL.FILE_UPLOAD.FOLDERS.DRIVING_LICENSE').'/'.$userId);
    }

    /**
     * Suspend a clinic by setting the `is_suspended` flag to true.
     *
     * Also sends a suspension notification email to the clinic user.
     *
     * @param Request $request
     * @param int     $clinicId
     *
     * @return Clinic|null
     */
    public function suspendClinic(Request $request, int $clinicId): ?Clinic
    {
        $clinic = $this->clinicRepository->findOrFail($clinicId);

        if (! empty($request->note)) {
            $this->noteService->addClinicNote($clinic, $request->note, config('constants.NOTE_ACTIVITY.SUSPEND'));
        }

        try {
            Mail::to($clinic->user->email)->send(new SuspendMail([
                'name' => $clinic->user->first_name,
            ]));
        } catch (Exception $e) {
        }

        return $this->clinicRepository->update($clinic->id, ['is_suspended' => true]);
    }

    /**
     * Reactivate a suspended clinic by setting the `is_suspended` flag to false.
     *
     * @param Request $request
     * @param int     $clinicId
     *
     * @return Clinic|null
     */
    public function reactivateClinic(Request $request, int $clinicId): ?Clinic
    {
        $clinic = $this->clinicRepository->findOrFail($clinicId);

        if (! empty($request->note)) {
            $this->noteService->addClinicNote($clinic, $request->note, config('constants.NOTE_ACTIVITY.REACTIVATE'));
        }

        return $this->clinicRepository->update($clinic->id, ['is_suspended' => false]);
    }

    /**
     * Calculate clinic revenue summary for a user.
     *
     * Returns formatted revenue:
     * - Since joining
     * - In the last 30 days
     * - Upcoming invoices
     *
     * @param int $clinicId
     *
     * @return array
     */
    public function calculateClinicRevenue(int $clinicId): array
    {
        return [
            'since_joining' => CommonHelper::formatPricing($this->recallHistoryRepository->getRevenue($clinicId)),
            'last_30_days' => CommonHelper::formatPricing($this->recallHistoryRepository->getRevenue($clinicId, 30)),
            'upcoming' => CommonHelper::formatPricing($this->recallHistoryRepository->getProjectedAdditionalRevenue($clinicId)),
        ];
    }

    /**
     * Get subscription and billing details for the user's latest subscription.
     *
     * Includes:
     * - Plan name, status, start/cancel date
     * - Billing cycle dates
     * - Last paid invoice and upcoming invoice
     * - Default card info
     *
     * @param User $user
     *
     * @return array
     */
    public function getUserSubscriptionDetails(User $user): array
    {
        $subscription = $this->userSubscriptionPlanRepository->getLatestSubscriptionWithRelations($user->id);

        if (! $subscription) {
            return [];
        }

        // Get latest billing cycle
        $latestBillingHistory = $subscription->billingHistories->sortByDesc('cycle_start_date')->first();

        // Get last paid invoice
        $lastPaidInvoice = $latestBillingHistory?->invoices->where('status', 'paid')->sortByDesc('invoice_date')->first();

        // Get upcoming invoice
        $upcomingInvoice = $latestBillingHistory?->invoices->where('status', 'open')->sortBy('invoice_date')->first();

        // Get default card (will add in to payment Method Repo once that branch merged)
        $defaultCard = $this->paymentMethodRepository->getDefaultPaymentMethodsByUserId($user->id);

        return [
            'subscription_plan' => $subscription->subscriptionPlan->name ?? null,
            'subscription_status' => $subscription->subscription_status,
            'subscription_start_date' => $subscription->subscription_start_date,
            'cancelled_at' => $subscription->cancelled_at,
            'billing_cycle' => $latestBillingHistory
                ? $latestBillingHistory->cycle_start_date.' to '.$latestBillingHistory->cycle_end_date
                : null,
            'last_paid_amount' => $lastPaidInvoice->amount ?? null,
            'next_billing_date' => $subscription->subscription_end_date ? ($subscription->isFreePlan() ? $subscription->subscription_end_date->addDay() : $subscription->subscription_end_date) : null,
            'card_last_digits' => $defaultCard->card_last_digits ?? null,
            'card_brand' => $defaultCard->card_brand ?? null,
        ];
    }

    /**
     * Get all invoices for a given user.
     *
     * @param User $user
     *
     * @return \Illuminate\Support\Collection
     */
    public function getClinicInvoiceDetails(User $user)
    {
        return $this->invoiceRepository->getInvoicesByUser($user->id);
    }

    /**
     * Get SMS and patient opt-out/deactivation statistics for a clinic.
     *
     * Returns message counts per type and patient counts with percentages.
     *
     * @param int $clinicId
     *
     * @return array
     */
    public function getSmsAndPatientStats(int $clinicId): array
    {
        $sentMessagesQuery = $this->messageRepository->getSentMessagesQueryByClinic($clinicId);

        $messageTypes = array_slice(config('constants.GLOBAL.MESSAGE'), 0, 3);

        $messages = [];

        foreach ($messageTypes as $key => $messageType) {
            // code...
            $messages[$messageType] = (clone $sentMessagesQuery)->where('message_type', $messageType)->sum('segments');
        }

        $totalPatients = $this->patientRepository->countByClinic($clinicId);
        $activePatients = $this->patientRepository->countActivePatientByClinic($clinicId);
        $optedOut = $this->patientRepository->countOptedOutByClinic($clinicId);
        $deactivated = $this->patientRepository->countDeactivatedByClinic($clinicId);

        $optedOutPercent = ($activePatients + $optedOut) > 0 ? round(($optedOut / $activePatients + $optedOut) * 100, 2) : 0;
        $deactivatedPercent = $totalPatients > 0 ? round(($deactivated / $totalPatients) * 100, 2) : 0;

        return [
            'campaigns' => $this->campaignRepository->countCampaignsByClinic($clinicId) ?? 0,
            'overdue_campaigns' => $this->campaignRepository->countOverdueCampaignsByClinic($clinicId) ?? 0,
            'promotional_campaigns' => $this->campaignRepository->countPromotionalCampaignsByClinic($clinicId) ?? 0,
            'messages' => $messages,
            'patients' => [
                'total_patients' => $totalPatients,
                'opt_deactivate_patients' => $optedOut + $deactivated,
                'opted_out' => [
                    'count' => $optedOut,
                    'percent' => $optedOutPercent,
                ],
                'deactivated' => [
                    'count' => $deactivated,
                    'percent' => $deactivatedPercent,
                ],
            ],
        ];
    }

    /**
     * Get clinic usage and patient progress analytics.
     *
     * Includes:
     * - Overdue patient stats (at signup and now)
     * - % of overdue patients
     * - Overdue patients with no SMS
     * - Last import, login, campaign dates
     * - SMS balance
     *
     * @param Clinic $clinic
     *
     * @return array
     */
    public function getUsageAndProgress(Clinic $clinic): array
    {
        $firstImport = $this->importRepository->getFirstImportByClinic($clinic->id);

        $totalPatientsAtSignup = ! empty($firstImport) ? $this->patientRepository->getTotalPatientsAtDate($clinic->id, $firstImport->created_at) : 0;
        $totalPatientsNow = $this->patientRepository->countByClinic($clinic->id);

        $overdueAtSignup = $this->patientRepository->getOverdueAtSignup($clinic->id);
        $overdueNow = $this->patientRepository->getOverdueNow($clinic->id);

        $percentAtSignup = $totalPatientsAtSignup > 0 ? round(($overdueAtSignup / $totalPatientsAtSignup) * 100, 2) : 0;
        $percentNow = $totalPatientsNow > 0 ? round(($overdueNow / $totalPatientsNow) * 100, 2) : 0;

        $lastCampaignData = $this->messageRepository->getLastCampaignSentDate($clinic->id);

        return [
            'patients_overdue_at_signup' =>  $overdueAtSignup,
            'patients_overdue_now' =>  $overdueNow,
            'percent_overdue_at_signup' => $percentAtSignup,
            'percent_overdue_now' => $percentNow,
            'overdue_no_sms' => $this->patientRepository->getOverdueWithoutSms($clinic->id),
            'overdue_no_sms_2_months' => $this->patientRepository->getOverdueNoSmsInTwoMonths($clinic->id),
            'last_import_date' => CommonHelper::formatDate($this->importRepository->getLastImportDate($clinic->id)),
            'last_logged_in' => CommonHelper::formatDate($clinic->user->last_logged_in_at),
            'last_campaign_sent' => ! empty($lastCampaignData) ? CommonHelper::formatDate($lastCampaignData->created_at) : '',
            'last_campaign_type' => ! empty($lastCampaignData) ? ucfirst(strtolower($lastCampaignData->message_type)) : '',
            'sms_balance' => $this->subscriptionBillingHistoryRepository->getLatestSmsBalance($clinic->user_id),
        ];
    }

    /**
     * Get all details related to a specific clinic.
     *
     * @param int $id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return array
     */
    public function getClinicDetails(int $id): array
    {
        $clinic = $this->clinicRepository->findOrFail($id);
        $user = $clinic->user;

        return [
            'clinic' => $clinic,
            'clinic_revenue' => $this->calculateClinicRevenue($clinic->id),
            'subscription_details' => $this->getUserSubscriptionDetails($user),
            'invoices' => $this->getClinicInvoiceDetails($user),
            'sms_statistics' => $this->getSmsAndPatientStats($clinic->id),
            'usage' => $this->getUsageAndProgress($clinic),
            'notes' => $clinic->notes,
        ];
    }

    /**
     * Retrieve a clinic by its ID.
     *
     * @param int $id The ID of the clinic to retrieve.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the clinic is not found.
     *
     * @return Clinic The clinic model instance.
     */
    public function getClinic(int $id): Clinic
    {
        return $this->clinicRepository->findOrFail($id);
    }

    /**
     * Update the clinic and its associated user data.
     *
     * @param Request $request The request data or array containing update fields.
     * @param int     $id      The ID of the clinic to update.
     *
     * @return void
     */
    public function updateClinic(Request $request, int $id)
    {
        $clinic = $this->getClinic($id);
        if ($clinic) {
            $this->userRepository->update($clinic->user_id, $request->only('first_name', 'last_name'));
            $this->clinicRepository->update($clinic->id, $request->only('address'));
        }
    }
}
