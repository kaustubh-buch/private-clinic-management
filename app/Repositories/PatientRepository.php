<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Repositories\Contracts\PatientRepositoryInterface;

class PatientRepository implements PatientRepositoryInterface
{
    /**
     * Retrieve all patients with pagination.
     *
     * @param int $perPage Number of patients per page (default: 10)
     * @return \Illuminate\Pagination\Paginator
     */
    public function getAll($perPage = 10)
    {
        return Patient::paginate($perPage);
    }

    /**
     * Retrieve a patient by ID.
     * Throws ModelNotFoundException if not found.
     *
     * @param int $id Patient ID
     * @return \App\Models\Patient
     */
    public function getById($id)
    {
        return Patient::findOrFail($id);
    }

    /**
     * Create a new patient record in the database.
     *
     * @param array $data Patient data containing first_name, last_name, email, contact_number
     * @return \App\Models\Patient
     */
    public function create(array $data)
    {
        return Patient::create($data);
    }

    /**
     * Update an existing patient record.
     *
     * @param int $id Patient ID
     * @param array $data Updated patient data
     * @return \App\Models\Patient
     */
    public function update($id, array $data)
    {
        $patient = Patient::findOrFail($id);
        $patient->update($data);
        return $patient;
    }

    /**
     * Delete a patient record (soft delete).
     *
     * @param int $id Patient ID
     * @return bool
     */
    public function delete($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();
        return true;
    }
}