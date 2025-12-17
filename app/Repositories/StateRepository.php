<?php

namespace App\Repositories;

use App\Models\State;

class StateRepository extends CommonRepository
{
    /**
     * StateRepository constructor.
     *
     * @param State $model
     */
    public function __construct(State $model)
    {
        parent::__construct($model);
    }
}
