<?php

namespace App\DataTables;

use App\Models\Insurance;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InsuranceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Insurance> $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('admin_status', function ($row) {
                return $row->admin_status ? ucfirst(strtolower($row->admin_status)) : '-';
            })
            ->addColumn('action', function (Insurance $insurance) {
                $id = $insurance->id;
                $actionOptions = [
                    'edit' => route('admin.insurance.edit', ['insurance' => $id]),
                    'delete' => route('admin.insurance.destroy', ['insurance' => $id]),
                ];

                return view('admin.components.action', compact('actionOptions'))->render();
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Insurance $model): QueryBuilder
    {
        return $model->newQuery()
            ->whereNull('clinic_id')
            ->select([
                'id',
                'abbreviation',
                'common_name',
                'admin_status',
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('insuranceTable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'processing' => true,
                'serverSide' => true,
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('abbreviation')->title('Insurance Abbreviation'),
            Column::make('common_name')->title('Name commonly referred to as'),
            Column::make('admin_status')->title('Status'),
            Column::computed('action')
                ->title('Actions')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'Insurance_'.date('YmdHis');
    }
}
