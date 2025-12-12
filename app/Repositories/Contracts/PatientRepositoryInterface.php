<?php

namespace App\Repositories\Contracts;

interface PatientRepositoryInterface
{
    /**
     * Retrieve all patients with pagination.
     *
     * @param int $perPage Number of patients per page
     * @return \Illuminate\Pagination\Paginator
     */
    public function getAll($perPage = 10);

    /**
     * Retrieve a patient by ID.
     *
     * @param int $id Patient ID
     * @return \App\Models\Patient
     */
    public function getById($id);

    /**
     * Create a new patient record.
     *
     * @param array $data Patient data
     * @return \App\Models\Patient
     */
    public function create(array $data);

    /**
     * Update an existing patient record.
     *
     * @param int $id Patient ID
     * @param array $data Updated patient data
     * @return \App\Models\Patient
     */
    public function update($id, array $data);

    /**
     * Delete a patient record.
     *
     * @param int $id Patient ID
     * @return bool
     */
    public function delete($id);
}