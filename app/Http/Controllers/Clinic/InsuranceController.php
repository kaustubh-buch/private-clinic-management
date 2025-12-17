<?php

namespace App\Http\Controllers\Clinic;

use App\DataTables\ClinicInsuranceDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\MoveInsuranceRequest;
use App\Http\Requests\Clinic\UpdateInsuranceFieldRequest;
use App\Repositories\ClinicRepository;
use App\Repositories\InsuranceRepository;
use App\Services\InsuranceService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InsuranceController extends Controller
{
    protected InsuranceRepository $insuranceRepository;

    protected ClinicRepository $clinicRepository;

    protected InsuranceService $insuranceService;

    protected $clinic;

    /**
     * InsuranceController constructor.
     *
     * @param InsuranceRepository $insuranceRepository
     * @param ClinicRepository    $clinicRepository
     * @param InsuranceService    $insuranceService
     */
    public function __construct(
        InsuranceRepository $insuranceRepository,
        ClinicRepository $clinicRepository,
        InsuranceService $insuranceService
    ) {
        $this->insuranceRepository = $insuranceRepository;
        $this->clinicRepository = $clinicRepository;
        $this->insuranceService = $insuranceService;
        $this->clinic = Auth::user()->clinics;

        if (! $this->clinic) {
            return redirect()->route('dashboard');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param ClinicInsuranceDataTable $clinicInsuranceDataTable
     *
     * @return View
     */
    public function index(
        ClinicInsuranceDataTable $clinicInsuranceDataTable
    ): View {
        $other = clone $clinicInsuranceDataTable;
        $other->insuranceType = config('constants.INSURANCE_TYPES.OTHER');

        $preferred = $clinicInsuranceDataTable->forClinic($this->clinic->id)->html();
        $other = $other->forClinic($this->clinic->id)->html();

        return view('clinic.pages.settings.insurance.index', compact('preferred', 'other'));
    }

    /**
     * Retrieve insurance data for the clinic based on the type (preferred or other).
     *
     * @param string                   $type
     * @param ClinicInsuranceDataTable $clinicInsuranceDataTable
     *
     * @return JsonResponse
     */
    public function data(
        string $type,
        ClinicInsuranceDataTable $clinicInsuranceDataTable
    ): JsonResponse {
        if ($type == config('constants.INSURANCE_TYPES.OTHER')) {
            $clinicInsuranceDataTable->insuranceType = config('constants.INSURANCE_TYPES.OTHER');

            return $clinicInsuranceDataTable->forClinic($this->clinic->id)->render();
        }

        return $clinicInsuranceDataTable->forClinic($this->clinic->id)->render();
    }

    /**
     * Move an insurance to a different category (preferred or other).
     *
     * @param MoveInsuranceRequest $request
     *
     * @return JsonResponse
     */
    public function moveCategory(MoveInsuranceRequest $request): JsonResponse
    {
        try {
            $this->insuranceService->moveCategory($request->id, $request->type);

            return response()->json(['success' => true]);
        } catch (ModelNotFoundException | AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e instanceof ModelNotFoundException ? Response::HTTP_NOT_FOUND : Response::HTTP_FORBIDDEN);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.global.something_went_wrong'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a specific field of an insurance record.
     *
     * @param UpdateInsuranceFieldRequest $request
     *
     * @return JsonResponse
     */
    public function updateField(UpdateInsuranceFieldRequest $request): JsonResponse
    {
        try {
            $insurance = $this->insuranceRepository->findOrFail($request->id);

            $this->authorize('belongsToClinic', $insurance);

            $data = [$request->field => $request->value];

            $updated = $this->insuranceRepository->update($request->id, $data);

            if (! $updated) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.alerts.insurance_update_failed'),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.message.insurance_not_found'),
            ], Response::HTTP_NOT_FOUND);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.alerts.unauthorized_action'),
            ], Response::HTTP_FORBIDDEN);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.global.something_went_wrong'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified insurance record from storage.
     *
     * @param string $id
     *
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $insurance = $this->insuranceRepository->findOrFail($id);

            $this->authorize('belongsToClinic', $insurance);

            if (! $this->insuranceRepository->delete($id)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.message.insurance_not_found'),
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.message.insurance_delete'),
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.message.insurance_not_found'),
            ], Response::HTTP_NOT_FOUND);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.alerts.unauthorized_action'),
            ], Response::HTTP_FORBIDDEN);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.global.something_went_wrong'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
