@extends('clinic.layout.front')
@section('page-title','Insurance')
@section('content')
    <div class="page-wrapper">
        <div class="page-inner-wrapper">
            <div class="insurance-page-wrapper white-card full-min-height-white-card">
                <div class="insurance-wrap">
                    <h2>{{ __('messages.page_texts.preferred_providers') }}</h2>
                    <div class="custom-table-wrapper">

                        {!! $preferred->table(['class' => 'display custom-table insurance-table']) !!}

                    </div>
                </div>
                <div class="insurance-wrap">
                    <h2>{{ __('messages.page_texts.other_insurances') }}</h2>
                    <div class="custom-table-wrapper">

                        {!! $other->table(['class' => 'display custom-table insurance-table']) !!}

                    </div>
                </div>
            </div>
        </div>
    </div>
@include('clinic.modals.delete-insurance-modal')
@endsection

@push('scripts')
    {!! $preferred->scripts() !!}
    {!! $other->scripts() !!}
    <script>

        $(document).ready(function () {
            const preferredTableSelector = '#insurance-preferred-table';
            const otherTableSelector = '#insurance-other-table';
            const deleteRoute = "{{ route('settings.insurance.destroy',':insurance') }}"
            const messages = {
                                no_insurances: `{!! __('messages.page_texts.no_insurances') !!}`,
                                no_preferred: `{!! __('messages.page_texts.no_preferred') !!}`,
                            };

            const preferredTable = $(preferredTableSelector).DataTable();
            const otherTable = $(otherTableSelector).DataTable();

            function updateEmptyMessage() {
                const preferredCount = preferredTable.data().count();
                const otherCount = otherTable.data().count();

                let message = '';
                if (preferredCount === 0 && otherCount === 0) {
                    message = `
                        <div class="no-data-message">
                            ${ messages.no_insurances }
                        </div>`;
                    $(preferredTableSelector).find('td.dt-empty').html(message);
                    $(otherTableSelector).find('td.dt-empty').html(message);
                } else if (preferredCount === 0 && otherCount > 0) {
                    message = `
                        <div class="no-data-message">
                            ${ messages.no_preferred }
                        </div>`;
                    $(preferredTableSelector).find('td.dt-empty').html(message);
                }
            }

            $(document).on('draw.dt', function () {
                $(`${preferredTableSelector} thead th`).removeAttr('title');
                $(`${otherTableSelector} thead th`).removeAttr('title');
                updateEmptyMessage();
            });

            function handleAjaxAction(url, method, data, successCallback, errorCallback, showLoader = true) {
                $.ajax({
                    url,
                    method,
                    showLoader,
                    data,
                    success: successCallback,
                    error: errorCallback || function (xhr) {
                        handleAjaxError(xhr);
                    }
                });
            }

            function placeCursorAtEndInput(el) {
                if (el.setSelectionRange) {
                    let len = el.value.length;
                    el.focus();
                    el.setSelectionRange(len, len);
                } else {
                    // For older browsers
                    el.value = el.value; // move cursor to end
                }
            }

            $(document).on('click', '.move-insurance', function (e) {
                e.preventDefault();
                const id = $(this).data('id');
                const type = $(this).data('type');
                handleAjaxAction("{{ route('settings.insurance.move') }}", "POST", { id, type }, function () {
                    preferredTable.ajax.reload();
                    otherTable.ajax.reload();
                }, function () {
                    showCustomToast("{{ __('messages.global.something_went_wrong') }}",'error',true);
                }, false);
            });

            $(document).on('click', '.delete-insurance-link', function () {
                const id = $(this).data('id');
                $('.delete-insurance-modal-btn').attr('data-id',id)
                openDefaultModal('delete-insurance-modal');
            });

            $('.delete-insurance-modal-btn').on('click',function(e){
                e.preventDefault();
                const id = $(this).attr('data-id');
                const url = deleteRoute.replace(":insurance",id);
                handleAjaxAction(url, "DELETE", {}, function (response) {
                    if (response.success) {
                        preferredTable.ajax.reload();
                        otherTable.ajax.reload();
                        showCustomToast(response.message,'success',true);
                        closeModal('delete-insurance-modal');
                        $('body,html').removeClass('modal-open');
                    } else {
                        showCustomToast(response.message,'error',true);
                    }
                });
            });

            $(document).on('click', '.edit-insurance-link', function (e) {
                e.preventDefault();
                $(this).closest('.threedot-menu-wrapper').removeClass('open-menu');
                const $row = $(this).closest('tr');
                const id = $(this).data('id');
                const $displayNameCell = $row.find('td.insurance-editable-cell');
                const currentText = $displayNameCell.text().trim();

                if ($displayNameCell.find('input').length) return;

                const inputHtml = `
                    <div class="form-group mb-0">
                        <input type="text" value="${currentText}" class="form-control form-control-sm editable-input" maxlength="{{ config('constants.MAX_LENGTH.COMMON_NAME') }}" />
                    </div>`;
                $displayNameCell.html(inputHtml);

                const $input = $displayNameCell.find('input');
                $input.focus();
                placeCursorAtEndInput($input[0]);

                $input.focus().on('blur', function () {

                    const newValue = $input.val().trim();
                    if (newValue && newValue !== currentText) {
                        handleAjaxAction("{{ route('settings.insurance.update-field') }}", "POST", {
                            id,
                            field: 'common_name',
                            value: newValue
                        }, function () {
                            $displayNameCell.html(newValue);
                        }, function () {
                            $displayNameCell.html(currentText);
                            showCustomToast("{{ __('messages.global.something_went_wrong') }}",'error',true);
                        }, false);
                    } else {
                        $displayNameCell.html(currentText);
                    }
                });

                $input.on('keydown', function (e) {
                    if (e.which === 13) {
                        $(this).blur();
                    }
                });
            });

            $('.insurance-table').on('draw.dt init.dt', function () {
                $(preferredTableSelector + ' thead th').eq(1).removeClass('text-no-wrap');
                $(otherTableSelector + ' thead th').eq(1).removeClass('text-no-wrap');
            });
        });
    </script>
@endpush
