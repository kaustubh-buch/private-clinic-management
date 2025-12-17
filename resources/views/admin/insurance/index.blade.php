@extends('admin.layouts.admin')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Insurance</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" title="Home">Dashboard</a>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Tabs Section -->
                <div class="card">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs" id="insuranceTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="dental-tab" data-bs-toggle="tab" href="#dental"
                                    role="tab" aria-controls="dental" aria-selected="true">{{ config('constants.GLOBAL.SOFTWARE.DENTAL_4_WINDOWS') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <!-- Tab panes -->
                        <div class="tab-content" id="insuranceTabsContent">
                            <div class="tab-pane fade show active flex-column d-flex" id="dental" role="tabpanel"
                                aria-labelledby="dental-tab">
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="{{ route('admin.insurance.create') }}" class="btn btn-primary">
                                        Add Insurance
                                    </a>
                                </div>

                                {{ $dataTable->table(['class' => 'table table-bordered table-striped']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@include('admin.modals.delete-confirmation-popup')
@push('scripts')
    {{ $dataTable->scripts() }}
    <script>
        $(document).ready(function() {
            $('body').on('click', '.open-delete-modal', function() {
                var deleteUrl = $(this).data('delete-url');
                $('#deleteForm').attr('action', deleteUrl);
                $('#deleteModal').modal('show');
            });
        });
    </script>
@endpush
