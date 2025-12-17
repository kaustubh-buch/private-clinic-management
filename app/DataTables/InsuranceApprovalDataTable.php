<?php

namespace App\DataTables;

use App\Repositories\InsuranceRepository;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InsuranceApprovalDataTable extends DataTable
{
    /**
     * InsuranceApprovalDataTable constructor.
     *
     * @param InsuranceRepository $insuranceRepository
     */
    public function __construct(protected InsuranceRepository $insuranceRepository)
    {
    }

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     *
     * @return EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($row) {
                $actionOptions = [
                    'insurance_approve' => route('admin.insurance.approvals.approve', ['id' => $row->id]),
                ];

                return view('admin.components.action', compact('actionOptions', 'row'))->render();
            })
            ->filterColumn('abbreviation', function ($query, $keyword) {
                $query->whereRaw('LOWER(clinic_insurance.abbreviation) LIKE ?', ['%'.strtolower($keyword).'%']);
            })
            ->filterColumn('common_name', function ($query, $keyword) {
                $query->whereRaw('LOWER(clinic_insurance.common_name) LIKE ?', ['%'.strtolower($keyword).'%']);
            })
            ->filterColumn('clinic_name', function ($query, $keyword) {
                $query->whereRaw('LOWER(cl.name) LIKE ?', ['%'.strtolower($keyword).'%']);
            })
            ->filterColumn('contact_no', function ($query, $keyword) {
                $query->whereRaw('LOWER(cl.contact_no) LIKE ?', ['%'.strtolower($keyword).'%']);
            })
            ->orderColumn('abbreviation', function ($query, $order) {
                $query->orderBy('clinic_insurance.abbreviation', $order);
            })
            ->orderColumn('common_name', function ($query, $order) {
                $query->orderBy('clinic_insurance.common_name', $order);
            })
            ->orderColumn('clinic_name', function ($query, $order) {
                $query->orderBy('cl.name', $order);
            })
            ->orderColumn('contact_no', function ($query, $order) {
                $query->orderBy('cl.contact_no', $order);
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder
     */
    public function query(): QueryBuilder
    {
        return $this->insuranceRepository->pendingInsuranceApproval();
    }

    /**
     * Build the HTML structure for the DataTable.
     *
     * @return HtmlBuilder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('insuranceapproval-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'searching' => true,
                'info' => true,
                'autoWidth' => false,
                'processing' => false,
            ]);
    }

    /**
     * Get the DataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            Column::make('abbreviation')->title('Insurance Abbreviation'),
            Column::make('common_name')->title('Name commonly referred to as'),
            Column::make('clinic_name')->title('Clinic name'),
            Column::make('contact_no')->title('Contact number'),
            Column::computed('action')
                ->width(100)
                ->addClass('text-center'),
        ];
    }
}
