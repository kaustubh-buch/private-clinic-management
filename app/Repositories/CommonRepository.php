<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class CommonRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Reterives all the records of table.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * Reterives all the records of table with pagination.
     *
     * @param $limit no of records to be displayed in each page.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPaginate($limit = 8)
    {
        return $this->model->paginate($limit);
    }

    /**
     * Store the data into database.
     *
     * @param array $data array of data to be inserted in database.
     *
     * @return Model
     */
    public function store(array $data)
    {
        $model = $this->model->create($data);

        return $model;
    }

    /**
     * Find a record by its primary key.
     *
     * @param mixed $id The primary key of the record to find.
     *
     * @return Model|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Find a record by its primary key or throw an exception if not found.
     *
     * @param mixed $id The primary key of the record to find.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return Model
     */
    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Find record and update record into database.
     *
     * @param int   $id   Id of the record to be updated.
     * @param array $data Array of the records to be update.
     *
     * @return Model|null
     */
    public function update($id, array $data)
    {
        $record = $this->model->find($id);
        if ($record) {
            $record->update($data);

            return $record;
        }

        return null;
    }

    /**
     * Delets the records from the database.
     *
     * @param int $id Id of the record to be deleted.
     *
     * @return bool
     */
    public function destroy($id)
    {
        $record = $this->model->find($id);

        if ($record) {
            $record->delete();

            return true;
        }

        return false;
    }

    /**
     * Find a record by one or more fields.
     *
     * @param array $fields An associative array of field names and values to search for.
     *                      Keys represent field names, and values represent either single values
     *                      or arrays with the value and optional operator.
     *                      Example: ['name' => 'John', 'age' => [30, '>']]
     *
     * @return mixed The first matching record, or null if no record is found.
     */
    public function findByFields(array $fields)
    {
        return $this->model->where(function ($query) use ($fields) {
            foreach ($fields as $fieldName => $value) {
                if (is_array($value)) {
                    // If the value is an array, assume the operator is provided as the second element
                    $operator = isset($value[1]) ? $value[1] : '=';
                    $query->where($fieldName, $operator, $value[0]);
                } else {
                    $query->where($fieldName, '=', $value);
                }
            }
        })->latest()->first();
    }

    /**
     * Get a key value from the model by key name.
     *
     * @param string $columnName
     * @param string $value
     *
     * @return mixed|null
     */
    public function getByColumn($columnName, $value)
    {
        return $this->model->where($columnName, $value)->first();
    }

    /**
     * Update or create a record if it doesn't exist based on the provided fields.
     *
     * @param array $checkFields The fields to check for existence.
     * @param array $data        The data to update or create.
     *
     * @return mixed|null
     */
    public function updateOrCreate(array $checkFields, array $data)
    {
        return $this->model->updateOrCreate($checkFields, $data);
    }

    /**
     * Get the underlying model instance used by the repository.
     *
     * @return Model The underlying model instance
     */
    public function getModel()
    {
        return $this->model;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function delete($id)
    {
        $record = $this->model->find($id);

        if (! $record) {
            return false;
        }

        return $record->delete();
    }
}
