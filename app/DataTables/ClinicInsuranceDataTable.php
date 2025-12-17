<?php

namespace App\DataTables;

use App\Repositories\InsuranceRepository;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ClinicInsuranceDataTable extends DataTable
{
    protected $clinicId;

    public $insuranceType;

    /**
     * ClinicInsuranceDataTable constructor.
     *
     * @param InsuranceRepository $insuranceRepository
     */
    public function __construct(protected InsuranceRepository $insuranceRepository)
    {
        $this->insuranceRepository = $insuranceRepository;
        $this->insuranceType = config('constants.INSURANCE_TYPES.PREFERRED');
    }

    /**
     * Set the clinic ID for the DataTable.
     *
     * @param int $clinicId
     *
     * @return $this
     */
    public function forClinic(int $clinicId): self
    {
        $this->clinicId = $clinicId;

        return $this;
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
            ->addColumn('move', function ($row) {
                $preferredOnly = $this->insuranceType == $this->isPreferred();
                $column = 'move';

                return view('clinic.pages.settings.insurance.partials.action', compact('row', 'preferredOnly', 'column'))->render();
            })
            ->addColumn('action', function ($row) {
                $column = 'action';

                return view('clinic.pages.settings.insurance.partials.action', compact('row', 'column'))->render();
            })
            ->orderColumn('abbreviation', function ($query, $order) {
                $query->orderBy('abbreviation', $order);
            })
            ->orderColumn('common_name', function ($query, $order) {
                $query->orderBy('common_name', $order);
            })
            ->rawColumns(['action', 'common_name', 'move'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder
     */
    public function query(): QueryBuilder
    {
        if ($this->isPreferred()) {
            return $this->insuranceRepository->buildPreferredProviderInsuranceDataTableQuery($this->clinicId);
        }

        return $this->insuranceRepository->buildOtherInsuranceDataTableQuery($this->clinicId);
    }

    /**
     * Build the HTML structure for the DataTable.
     *
     * @return HtmlBuilder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId($this->getTableId())
            ->columns($this->getColumns())
            ->minifiedAjax(route('settings.insurance.data', $this->insuranceType))
            ->orderBy(0, 'asc')
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'processing' => false,
                'serverSide' => true,
                'paging' => false,
                'info' => false,
                'searching' => false,
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
            Column::make('abbreviation')->title(__('messages.page_texts.insurance_abbreviation').' <span class="sortable-icon"></span>')->addClass('text-no-wrap sortable-column')->orderable(true),
            Column::make('common_name')->title(' <div class="tooltip-wrap">
                <div class="text-no-wrap">'.__('messages.page_texts.insurance_common_name').'</div>
                <span class="sortable-icon"></span>
                <div class="info-tooltip">
                    <div class="tooltip-icon"><img
                            src="'.asset('front/images/info-icon.svg').'" alt="info-icon">
                    </div>
                    <div class="tooltip-text">'.__('messages.page_texts.common_name_info').'<div class="tooltip-arrow" data-arrow></div>
                    </div>
                </div>
            </div>')->addClass('text-no-wrap sortable-column insurance-editable-cell')->orderable(true),
            Column::computed('move')->title('<div class="tooltip-wrap">
                <div class="text-no-wrap">'.__('messages.page_texts.group').'</div>
                <div class="info-tooltip small-tooltip">
                    <div class="tooltip-icon"><img
                            src="'.asset('front/images/info-icon.svg').'" alt="info-icon">
                    </div>
                    <div class="tooltip-text">'.__('messages.page_texts.move_info').'<div class="tooltip-arrow" data-arrow></div>
                    </div>
                </div>
            </div>')->orderable(false),

            Column::computed('action')
                ->title('')
                ->addClass('action-col text-center')
                ->orderable(false),
        ];
    }

    /**
     * Determine if the insurance is of preferred type.
     *
     * @return bool
     */
    public function isPreferred()
    {
        return $this->insuranceType == config('constants.INSURANCE_TYPES.PREFERRED');
    }

    /**
     * Get the HTML table ID for this insurance type.
     *
     * @return string
     */
    public function getTableId()
    {
        return 'insurance-'.Str::lower($this->insuranceType).'-table';
    }
}
