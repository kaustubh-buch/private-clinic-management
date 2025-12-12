<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Repositories\Contracts\PatientRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PatientController extends Controller
{
    protected PatientRepositoryInterface $patientRepository;

    /**
     * Constructor to inject the PatientRepository dependency.
     *
     * @param PatientRepositoryInterface $patientRepository
     */
    public function __construct(PatientRepositoryInterface $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    /**
     * Display a paginated list of all patients.
     *
     * @return View
     */
    public function index(): View
    {
        $patients = $this->patientRepository->getAll(10);
        return view('patients.index', compact('patients'));
    }

    /**
     * Display the form for creating a new patient.
     *
     * @return View
     */
    public function create(): View
    {
        return view('patients.create');
    }

    /**
     * Store a newly created patient in the database.
     *
     * @param StorePatientRequest $request
     * @return RedirectResponse
     */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $this->patientRepository->create($request->validated());

        return redirect()
            ->route('patients.index')
            ->with('success', 'Patient created successfully.');
    }

    /**
     * Display the details of a specific patient.
     *
     * @param int $id Patient ID
     * @return View
     */
    public function show($id): View
    {
        $patient = $this->patientRepository->getById($id);
        return view('patients.show', compact('patient'));
    }

    /**
     * Display the form for editing a specific patient.
     *
     * @param int $id Patient ID
     * @return View
     */
    public function edit($id): View
    {
        $patient = $this->patientRepository->getById($id);
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update a specific patient record in the database.
     *
     * @param UpdatePatientRequest $request
     * @param int $id Patient ID
     * @return RedirectResponse
     */
    public function update(UpdatePatientRequest $request, $id): RedirectResponse
    {
        $this->patientRepository->update($id, $request->validated());

        return redirect()
            ->route('patients.index')
            ->with('success', 'Patient updated successfully.');
    }

    /**
     * Delete a specific patient record from the database.
     *
     * @param int $id Patient ID
     * @return RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        $this->patientRepository->delete($id);

        return redirect()
            ->route('patients.index')
            ->with('success', 'Patient deleted successfully.');
    }
}