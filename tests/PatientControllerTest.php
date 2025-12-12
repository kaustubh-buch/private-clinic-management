<?php
<?php

namespace Tests\Feature;

use App\Http\Controllers\PatientController;
use App\Models\Patient;
use App\Repositories\Contracts\PatientRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

    private PatientRepositoryInterface|MockInterface $patientRepository;
    private PatientController $controller;

    /**
     * Set up the test environment before each test.
     * Initializes mocks and controller instance.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->patientRepository = $this->mock(PatientRepositoryInterface::class);
        $this->controller = new PatientController($this->patientRepository);
    }

    /**
     * Test that the index method returns a paginated list of patients.
     * Verifies that the view is rendered with patient data.
     *
     * @return void
     */
    public function test_index_displays_paginated_patients()
    {
        $patients = Patient::factory()->count(3)->create();
        
        $paginator = new LengthAwarePaginator(
            $patients,
            $patients->count(),
            10,
            1,
            ['path' => '/patients']
        );

        $this->patientRepository
            ->shouldReceive('getAll')
            ->once()
            ->with(10)
            ->andReturn($paginator);

        $response = $this->get('/patients');

        $response->assertStatus(200);
        $response->assertViewIs('patients.index');
        $response->assertViewHas('patients');
    }

    /**
     * Test that the create method displays the patient creation form.
     * Verifies that the create view is rendered correctly.
     *
     * @return void
     */
    public function test_create_displays_patient_form()
    {
        $response = $this->get('/patients/create');

        $response->assertStatus(200);
        $response->assertViewIs('patients.create');
    }

    /**
     * Test that the store method creates a new patient.
     * Verifies that valid data is passed to the repository and user is redirected.
     *
     * @return void
     */
    public function test_store_creates_new_patient_successfully()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'contact_number' => '1234567890',
        ];

        $patient = Patient::factory()->create($data);

        $this->patientRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($patient);

        $response = $this->post('/patients', $data);

        $response->assertRedirect(route('patients.index'));
        $response->assertSessionHas('success', 'Patient created successfully.');
    }

    /**
     * Test that the store method fails with invalid data.
     * Verifies that validation errors are returned for missing required fields.
     *
     * @return void
     */
    public function test_store_fails_with_invalid_data()
    {
        $invalidData = [
            'first_name' => '',
            'email' => 'invalid-email',
        ];

        $response = $this->post('/patients', $invalidData);

        $response->assertSessionHasErrors(['first_name', 'email']);
    }

    /**
     * Test that the show method displays a specific patient.
     * Verifies that patient data is passed to the view.
     *
     * @return void
     */
    public function test_show_displays_specific_patient()
    {
        $patient = Patient::factory()->create();

        $this->patientRepository
            ->shouldReceive('getById')
            ->once()
            ->with($patient->id)
            ->andReturn($patient);

        $response = $this->get(route('patients.show', $patient->id));

        $response->assertStatus(200);
        $response->assertViewIs('patients.show');
        $response->assertViewHas('patient', $patient);
    }

    /**
     * Test that the show method returns 404 for non-existent patient.
     * Verifies that ModelNotFoundException is handled properly.
     *
     * @return void
     */
    public function test_show_returns_404_for_non_existent_patient()
    {
        $this->patientRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andThrow(new \Illuminate\Database\Eloquent\ModelNotFoundException());

        $response = $this->get(route('patients.show', 999));

        $response->assertStatus(404);
    }

    /**
     * Test that the edit method displays the patient edit form.
     * Verifies that patient data is loaded in the form.
     *
     * @return void
     */
    public function test_edit_displays_patient_edit_form()
    {
        $patient = Patient::factory()->create();

        $this->patientRepository
            ->shouldReceive('getById')
            ->once()
            ->with($patient->id)
            ->andReturn($patient);

        $response = $this->get(route('patients.edit', $patient->id));

        $response->assertStatus(200);
        $response->assertViewIs('patients.edit');
        $response->assertViewHas('patient', $patient);
    }

    /**
     * Test that the update method updates an existing patient.
     * Verifies that updated data is passed to the repository.
     *
     * @return void
     */
    public function test_update_updates_patient_successfully()
    {
        $patient = Patient::factory()->create();

        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'contact_number' => '9876543210',
        ];

        $updatedPatient = $patient->replicate()->fill($data);

        $this->patientRepository
            ->shouldReceive('update')
            ->once()
            ->with($patient->id, $data)
            ->andReturn($updatedPatient);

        $response = $this->put(route('patients.update', $patient->id), $data);

        $response->assertRedirect(route('patients.index'));
        $response->assertSessionHas('success', 'Patient updated successfully.');
    }

    /**
     * Test that the update method fails with invalid data.
     * Verifies that validation errors are returned.
     *
     * @return void
     */
    public function test_update_fails_with_invalid_data()
    {
        $patient = Patient::factory()->create();
        $patientId = $patient->id;

        $invalidData = [
            'first_name' => '',
            'email' => 'invalid-email',
        ];

        // Ensure the edit page can be rendered when validation fails (redirect back).
        $this->patientRepository
            ->shouldReceive('getById')
            ->once()
            ->with($patientId)
            ->andReturn($patient);

        // Set the "previous" URL to the edit form so the validator redirects back there.
        $response = $this->from(route('patients.edit', $patientId))
                         ->put(route('patients.update', $patientId), $invalidData);

        $response->assertSessionHasErrors(['first_name', 'email']);
    }

    /**
     * Test that the destroy method deletes a patient.
     * Verifies that the patient is removed and user is redirected.
     *
     * @return void
     */
    public function test_destroy_deletes_patient_successfully()
    {
        $patient = Patient::factory()->create();

        $this->patientRepository
            ->shouldReceive('delete')
            ->once()
            ->with($patient->id)
            ->andReturn(true);

        $response = $this->delete(route('patients.destroy', $patient->id));

        $response->assertRedirect(route('patients.index'));
        $response->assertSessionHas('success', 'Patient deleted successfully.');
    }

    /**
     * Test that the destroy method handles deletion failure gracefully.
     * Verifies that ModelNotFoundException is handled properly.
     *
     * @return void
     */
    public function test_destroy_returns_404_for_non_existent_patient()
    {
        $this->patientRepository
            ->shouldReceive('delete')
            ->once()
            ->with(999)
            ->andThrow(new \Illuminate\Database\Eloquent\ModelNotFoundException());

        $response = $this->delete(route('patients.destroy', 999));

        $response->assertStatus(404);
    }
}