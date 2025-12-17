<?php

namespace App\Repositories;

use App\Models\InvoiceItem;

class InvoiceItemRepository extends CommonRepository
{
    /**
     * Create a new class instance.
     *
     * @param InvoiceItem $model
     */
    public function __construct(InvoiceItem $model)
    {
        parent::__construct($model);
    }
}
