@extends('clinic.layout.front')
@push('css')
    <link href="{{ asset('front/css/quill.snow.css') }}" rel="stylesheet" />
@endpush
@section('page-title', 'Templates')
@section('content')
    <div class="page-wrapper">
        <div class="page-inner-wrapper">
            <div class="templates-page-wrapper white-card full-min-height-white-card">
                <div class="custom-tab-wrapper">
                    <div class="tab-search-wrapper d-flex justify-between items-center">
                        <div class="tab-menu">
                            <ul>
                                <li class="tab-list @if ($isFirstTabActive) tab-active @endif"><a href="#"
                                        data-link="overdue-recalls">
                                        {{ __('messages.page_texts.overdue_recalls') }}</a></li>
                                <li class="tab-list @if ($isFirstTabActive == false) tab-active @endif"><a href="#"
                                        data-link="promotional-templates">
                                        {{ __('messages.page_texts.promotional_templates') }}</a></li>
                            </ul>
                        </div>
                        <button class="new-template-btn primary-btn small-btn w-auto open-template-btn"
                            @if ($isFirstTabActive) style="display: none" @endif>{{ __('messages.labels.new_template') }}</button>
                    </div>
                    <div class="tab-content-wrapper">
                        <div class="tab-content @if ($isFirstTabActive) tab-active @endif"
                            data-tab="overdue-recalls">
                            <div class="custom-table-wrapper">
                                <table id="overdue-recalls-table"
                                    class="display custom-table expandable-table overduecalls-table">
                                    <thead>
                                        <tr>
                                            <th class="dt-control"></th>
                                            <th></th>
                                            <th class="add-template-col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($templateCategories as $category)
                                            <tr data-id="{{ $category->id }}">
                                                <td class="dt-control"></td>
                                                <td>
                                                    <div class="row-title">{{ $category->name }}</div>
                                                </td>
                                                @php
                                                    $templateCount = $category->overdueTemplates?->count() ?? 0;
                                                    $maxTempLimit = config('constants.OVERDUE_TEMPLATE_LIMIT');
                                                @endphp
                                                <td class="add-template-col">
                                                    <button class="primary-border-btn sm border-btn open-template-btn"
                                                        data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                                                        data-template-count="{{ $templateCount }}">+ Add template</button>
                                                </td>
                                            </tr>

                                            <template id="overdue-child-row-template">
                                                <div class="template-accordion-parent" id="overdue_templates"></div>
                                            </template>
                                        @endforeach
                                        <div id="category-template-data" data-templates='@json($templateCategories)'>
                                        </div>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-content @if ($isFirstTabActive == false) tab-active @endif"
                            data-tab="promotional-templates">
                            <div class="custom-table-wrapper">
                                <table id="promotional-template-table"
                                    class="display custom-table expandable-table promotional-template-table">
                                    <thead>
                                        <tr>
                                            <th class="dt-control"></th>
                                            <th></th>
                                            <th class="action-col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($promotionalTemplates as $temp)
                                            <tr data-id="{{ $temp->id }}">
                                                <td class="dt-control"></td>
                                                <td>
                                                    <div class="row-title">{{ $temp->name }}</div>
                                                </td>
                                                <td class="text-center action-col">
                                                    <div class="threedot-menu-wrapper secondary">
                                                        <div class="threedot-menu-link">
                                                            <img src="{{ asset('front/images/vertical-three-dots.svg') }}"
                                                                alt="three-dot">
                                                        </div>
                                                        <div class="threedot-menu">
                                                            <ul>
                                                                <li>
                                                                    <a href="javascript:void(0)" title="Edit"
                                                                        class="edit-template-btn"
                                                                        data-id="{{ $temp->id }}"
                                                                        data-template-name="{{ $temp->name }}"
                                                                        data-template-message="{{ htmlentities($temp->formatted_message) }}">
                                                                        Edit
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="javascript:void(0)"
                                                                        class="delete-template red-text"
                                                                        data-id="{{ $temp->id }}"
                                                                        data-name="{{ $temp->name }}">
                                                                        Delete
                                                                    </a>
                                                                </li>
                                                                <form action="{{ url('/template') . '/' . $temp->id }}"
                                                                    method="POST" class="delete-template-form"
                                                                    style="display: none;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit">Delete</button>
                                                                </form>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                                <template id="promo-child-template">
                                                    <div class="promotional-child-content">
                                                        <div class="message-title">Message</div>
                                                        <div class="promotional_message_content"></div>
                                                    </div>
                                                </template>
                                            </tr>
                                        @endforeach
                                        <div id="promotional-template-data" data-templates='@json($promotionalTemplates)'>
                                        </div>

                                    </tbody>
                                </table>
                                <p class="error-message promotional-limit" style="display:none;">
                                    {{ __('messages.page_texts.promotional_max_limit') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('clinic.pages.template.modal')
    @include('clinic.pages.template.delete-template-confirmation')
@endsection
@push('scripts')
    <script>
        var bookingLink = "{{ $clinic->booking_link }}";
        var clinicName = "{{ $clinic->name }}";
        var clinicPhoneNumber = "{{ $clinic->mobile_no ? $clinic->mobile_no : $clinic->contact_no ?? '' }}";
    </script>
    <script src="{{ asset('front/js/quill.js') }}"></script>
    <script src="{{ asset('front/js/quill-setup.js') }}"></script>
    <script src="{{ asset('front/js/segmentsCalculator.js') }}"></script>
    <script src="{{ asset('front/js/cellcast-sms/character-count.js') }}"></script>
    <script src="{{ asset('front/js/emoji-button/allowed-emoji.js') }}"></script>
    <script src="{{ asset('front/js/cellcast-sms/emoji-picker.js') }}"></script>
    <script>
        (() => {
            const TEMPLATE_BASE_URL = @json(url('/templates'));
            const OVERDUE_TEMPLATE_LIMIT = {{ config('constants.OVERDUE_TEMPLATE_LIMIT') }};
            const backend_errors = "{{ $errors->any() }}";
            const PROMOTIONAL_TEMPLATE_LIMIT = {{ config('constants.PROMOTIONAL_TEMPLATE_LIMIT') }};
            let is_invalid = 0;
            const current_promotional_count = {{ count($promotionalTemplates) }};
            const lastSelectedTemplateCategory =
                "{{ $selectedTemplateCategoryId ? $selectedTemplateCategoryId : '' }}";
            let lastFocusedElement = 'message';
            let isFormSubmitted = false;
            const opt_text = "{!! __('messages.page_texts.opt_message_text') !!}";

            const initTemplateTable = ({
                tableSelector,
                dataSelector,
                pageLength = "{{ config('constants.TEMPLATE_LIST_LENGTH') }}",
                tableFormatter
            }) => {
                const $table = $(tableSelector);
                if (!$table.length) return null;

                const dt = new DataTable(tableSelector, {
                    paging: false,
                    pageLength,
                    lengthChange: false,
                    searching: false,
                    info: false,
                    ordering: false,
                    responsive: true,
                    language: {
                        emptyTable: tableSelector == '#promotional-template-table' ?
                            "No promotional template available" : 'No overdue template available',
                        paginate: {
                            previous: '<img src="{{ asset('front/images/prev-arrow.svg') }}">',
                            next: '<img src="{{ asset('front/images/next-arrow.svg') }}">'
                        }
                    },

                    layout: {
                        bottomEnd: {
                            paging: {
                                firstLast: false
                            }
                        }
                    }
                });

                dt.on('click', 'td.dt-control', (e) => {
                    const tr = e.target.closest('tr');
                    const row = dt.row(tr);
                    const {
                        id
                    } = tr.dataset;

                    dt.rows().every(function() {
                        if (this.child.isShown() && this.node() !== tr) {
                            this.child().find('.details-wrapper').slideUp(500, () => this.child
                                .hide());
                        }
                    });

                    if (row.child.isShown()) {

                        row
                            .child()
                            .find('.details-wrapper')
                            .slideUp(500, () => {
                                setTimeout(() => {
                                    row.child.hide();
                                }, 100); // delay in ms before hiding
                            });

                    } else {
                        const childHtml = `
                        <div class="details-wrapper" style="display:none;">
                            ${tableFormatter(id, dataSelector)}
                        </div>`;
                        row.child(childHtml, 'custom-child-row').show();
                        setTimeout(function() {
                            row.child().find('.details-wrapper').slideDown(500);
                        }, 10)
                    }
                });

                return dt;
            };




            const promotionalFormatter = (id, dataSelector) => {
                const list = $(dataSelector).data('templates') || [];
                const rec = list.find((t) => String(t.id) === String(id));
                return rec ?
                    `<div class="promotional-child-content">
                    <div class="message-title">Message</div>
                    <div class="promotional_message_content"><p class="text-pre-wrap">${rec.formatted_message}</p></div>
                    </div>` :
                    '<div>No message found.</div>';
            };

            const overdueFormatter = (id, dataSelector) => {
                const categories = $(dataSelector).data('templates') || [];
                const category = categories.find((c) => String(c.id) === String(id));
                if (!category || category.overdue_templates.length == 0)
                    return '<div class="template-accordion active"><div class="template-accordion-title">No templates available.</div></div>';
                const templates = category.overdue_templates;
                const hasDefaultTemplate = templates.some((template) => template.is_default && template.clinic_id);

                templates.map((t) => {
                    if (hasDefaultTemplate && t.is_default && !t.clinic_id) {
                        t.is_default = false;
                    }
                    return t;
                })
                templates.sort((a, b) => {
                    return b.is_default - a.is_default
                });
                const itemsHtml = templates
                    .map((t) => {
                        let isDefault = hasDefaultTemplate ? (t.is_default && t.clinic_id) : (t.is_default);
                        return `<div class="template-accordion ${isDefault ? 'active' : ''}">
                            <div class="template-accordion-title">
                                <div class="template-accordion-heading">${t.name}${ isDefault ? ' (Default)' : ''}</div>
                                <div class="threedot-menu-wrapper secondary ${!hasDefaultTemplate && !t.clinic_id ? 'd-none' : ''}">
                                    <div class="threedot-menu-link">
                                        <img src="{{ asset('front/images/vertical-three-dots.svg') }}" alt="three-dot">
                                    </div>
                                    <div class="threedot-menu">
                                        <ul>
                                            <li class="${ t.is_default && (t.clinic_id || !hasDefaultTemplate)
                                            ? 'd-none' : ''}">
                                                <a  href="${TEMPLATE_BASE_URL}/${t.id}/${category.id}/default"
                                                    class="set-default-template" title="Set as Default">
                                                    Set as Default
                                                </a>
                                            </li>
                                            <li class="${!t.clinic_id ? 'd-none' : ''}">
                                                <a href="javascript:void(0)"
                                                title="Edit"
                                                class="edit-template-btn"
                                                data-id="${t.id}"
                                                data-category-id="${category.id}"
                                                data-category-name="${category.name}"
                                                data-template-message="${escapeHtml(t.formatted_message)}"
                                                data-template-name="${t.name}"
                                                >
                                                Edit
                                                </a>
                                            </li>
                                            <li class="${!t.clinic_id ? 'd-none' : ''}">
                                                <a  href="javascript:void(0)"
                                                    class="delete-template red-text"
                                                    data-id="${t.id}"
                                                    data-name="${t.name}">
                                                    Delete
                                                </a>
                                            </li>
                                            <form action="{{ url('/template') . '/' }}${t.id}"
                                                method="POST" class="delete-template-form"
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit">Delete</button>
                                            </form>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="template-accordion-content"><p class="text-pre-wrap">${t.formatted_message}</p></div>
                    </div>`
                    }).join('');

                const errorHtml = category.overdue_templates.length >= OVERDUE_TEMPLATE_LIMIT ?
                    `<p class="error-message overdue-limit limit-error${category.id}" style="display:none;">
                        {{ __('messages.page_texts.overdue_max_limit') }}
                    </p>` :
                    '';

                return `
                <div class="template-accordion-parent">${itemsHtml}</div>
                ${errorHtml}`;
            };

            function handleSubmitButton() {
                const $submitBtn = $('#templateForm').find('button[type="submit"]');
                $submitBtn.prop('disabled', false);
                if (isFormSubmitted) {
                    if (!$('#message').text().includes('(Clinic Name)') ||
                        !$('#message').text().includes(opt_text)) {
                        $submitBtn.prop('disabled', true);
                    }
                }
            }

            let allowedPaste = false;

            $(document).ready(() => {
                @if ($errors->any())
                    handleBackendValidationErrors('#templateForm', @json($errors->getMessages()));
                @endif

                let formToSubmit = null;
                // DataTables ----------------------------------------------------------
                initTemplateTable({
                    tableSelector: '#promotional-template-table',
                    dataSelector: '#promotional-template-data',
                    pageLength: "{{ config('constants.TEMPLATE_LIST_LENGTH') }}",
                    tableFormatter: promotionalFormatter
                });

                initTemplateTable({
                    tableSelector: '#overdue-recalls-table',
                    dataSelector: '#category-template-data',
                    pageLength: "{{ config('constants.TEMPLATE_LIST_LENGTH') }}",
                    tableFormatter: overdueFormatter
                });

                if (lastSelectedTemplateCategory) {
                    $(`[data-id=${lastSelectedTemplateCategory}]`).find('.dt-control').click();
                }

                // Tabs ---------------------------------------------------------------
                $(document).on('click', '.tab-list a', function(e) {
                    e.preventDefault();
                    const target = $(this).data('link');

                    $('.tab-list').removeClass('tab-active');
                    $(this).closest('.tab-list').addClass('tab-active');

                    $('.tab-content')
                        .removeClass('tab-active')
                        .filter(`[data-tab="${target}"]`)
                        .addClass('tab-active');

                    $('.new-template-btn').toggle(target === 'promotional-templates');
                });


                //Template Edit--------------------------------------------------------
                $(document).on('click', '.edit-template-btn', function(e) {
                    e.preventDefault();
                    const $btn = $(this);
                    const id = $(this).data('id');
                    const templateName = $btn.data('template-name');
                    const templateMessage = $btn.data('template-message');
                    const category_id = $btn.data('category-id');
                    const category_name = $btn.data('category-name');
                    openTemplateModal(category_id, category_name, templateName, templateMessage, id);
                });

                // Form validation ----------------------------------------------------
                const $templateForm = $('#templateForm');
                $templateForm.validate({
                    ignore: [],
                    rules: {
                        name: {
                            required: true,
                            maxlength: "{{ config('constants.MAX_LENGTH.TEMPLATE_NAME') }}"
                        },
                        message: {
                            required: true,
                            maxlength: "{{ config('constants.MAX_LENGTH.TEMPLATE_MESSAGE') }}"
                        }
                    },
                    messages: {
                        name: {
                            required: "{{ __('messages.validation.template_name_required') }}"
                        },
                        message: {
                            required: "{{ __('messages.validation.template_message_required') }}",
                            maxlength: "{{ __('messages.validation.template_content_max_length') }}"
                        }
                    },
                    onfocusout: function(element) {
                        if ($(element).hasClass("is-invalid")) {
                            this.element(element);
                        }
                    },
                    onkeyup: function(element) {
                        if ($(element).hasClass("is-invalid")) {
                            this.element(element);
                        }
                    }
                });

                //  Modal function -------------------------------------------------------
                const openTemplateModal = (categoryId, categoryName, templateName = '', templateMessage = '',
                    templateId = null) => {
                    isFormSubmitted = false;
                    handleSubmitButton();
                    const form = $templateForm;
                    let isEditMode = templateId !== null && templateId !== '';

                    if (is_invalid != 0) {
                        $('p.error-message').remove();
                    }

                    $('.opt-out-message-text').hide();

                    is_invalid = 1;
                    if (!backend_errors && !isEditMode) {
                        form[0].reset();
                        form.validate().resetForm();
                    }

                    form.find('.is-invalid').removeClass('is-invalid');
                    $('#templateModal .is-invalid').removeClass('is-invalid');

                    //configuring form actions
                    if (isEditMode) {
                        form.attr('action', `{{ url('template') }}/${templateId}`);
                        $('#formMethod').val('PUT');
                    } else {
                        form.attr('action', `{{ route('template.store') }}`);
                        $('#formMethod').val('POST');
                    }

                    //setting up modal title and campaign type
                    const $title = $('#templateModalTitle');
                    if (categoryName !== undefined && categoryName != '') {
                        const titleText = isEditMode ?
                            `Editing Overdue Recalls (` + categoryName + `) Template: ${templateName}` :
                            `Add a new Overdue Recalls (${categoryName}) Template`;
                        $title.text(titleText);
                        $('#campaign_type_id').val(1); // Overdue
                    } else {
                        const titleText = isEditMode ?
                            `Editing Promotional Template: ${templateName}` :
                            `{{ __('messages.page_texts.new_promotional_template') }}`;
                        $title.text(titleText);
                        $('#campaign_type_id').val(2); // Promotional
                    }

                    //populating modal fields
                    $("#template_id").val(templateId)
                    $('#template_category_id').val(categoryId);
                    $('#name').val(templateName);
                    $('#category_name').val(categoryName);
                    $('#message').html('');
                    $('#hiddenMessage').val(templateMessage);

                    $('#templateModal')
                        .addClass('visible fadein')
                        .fadeIn();
                    $('body').addClass('modal-open');
                    initializeQuill('#message', decodeHtmlEntities(templateMessage),
                        "{{ __('messages.placeholders.template_message') }}");
                    $('#name').focus();
                };

                var $msg = $('.message-wrapper .message-block')
                    .css({
                        'outline': 'none',
                        'min-height': '100px'
                    });

                // $msg.on('keydown', updateCounts);

                $('#optOutBtn').on('click', function(e) {
                    e.preventDefault();
                    const $btn = $(this);
                    const $msg = $('.message-wrapper .message-block');

                    if ($btn.hasClass('disabled')) return;

                    $btn.addClass('disabled');
                    if (!$msg.html().includes("Reply 'STOP'")) {
                        setAllowedPaste(true);
                        addOptOutMessage("{{ __('messages.page_texts.opt_message_text') }}");
                        setAllowedPaste(false);
                        handleSubmitButton();
                    }
                    setTimeout(() => {
                        $btn.removeClass('disabled');
                    }, 200);
                });

                $('.menu-wrapper ul li a').on('click', function(e) {
                    e.preventDefault();

                    const fieldName = $(this).text().replace('_', ' ');
                    addLink(fieldName);
                    handleSubmitButton();
                });

                //backend validation handling---------------------------------------------
                if (backend_errors) {
                    openTemplateModal(
                        "{{ old('template_category_id') }}",
                        "{{ old('category_name') }}",
                        "{{ old('name') }}",
                        {!! json_encode(old('message')) !!},
                        "{{ old('id') }}");
                }

                $(document).on('click', '.open-template-btn', function(e) {
                    e.preventDefault();
                    if ($(this).data('name') !== undefined) {
                        $('.overdue-limit').hide()
                        var template_count = $(this).data('template-count')
                        if (template_count >= OVERDUE_TEMPLATE_LIMIT) {
                            $('.limit-error' + $(this).data('id')).show()
                        } else {
                            openTemplateModal($(this).data('id'), $(this).data('name'));
                        }
                    } else {
                        if (current_promotional_count >= PROMOTIONAL_TEMPLATE_LIMIT) {
                            $('.promotional-limit').show();
                            return;
                        }
                        openTemplateModal($(this).data('id'), $(this).data('name'));
                    }
                });
                //delete template----------------------------------------------
                $(document).on('click', '.delete-template', function(e) {
                    e.preventDefault();

                    const templateId = $(this).data('id');
                    const templateName = $(this).data('name');

                    $('#template-name-placeholder').text("Are you sure you want to delete template: " +
                        templateName + "?");
                    formToSubmit = $(this).closest('li').next('form.delete-template-form')[0];

                    // Show modal
                    $('#delete-modal')
                        .addClass('visible fadein')
                        .fadeIn();
                    $('body').addClass('modal-open');
                });
                $('[data-target="delete-modal"] .primary-btn').on('click', function(e) {
                    e.preventDefault();
                    if (formToSubmit) {
                        formToSubmit.submit();
                    }
                });

                $('#templateForm').on('submit', function(e) {
                    isFormSubmitted = true;
                    const $form = $(this);
                    const $submitBtn = $form.find('button[type="submit"]');
                    if ($submitBtn.prop('disabled')) {
                        e.preventDefault();
                        return false;
                    }
                    $submitBtn.prop('disabled', false);
                    $('#hiddenMessage').val(getFullTextIncludingEmbeds());
                    if (!$('#message').text().includes('(Clinic Name)') ||
                        !$('#message').text().includes(opt_text)) {
                        e.preventDefault();
                        $('.opt-out-message-text').show();
                        handleSubmitButton();
                        return false;
                    }
                });

                window.addEventListener('reactToBladeEvent', function(e) {
                    console.log(isNameFocus);
                    if (isNameFocus) {
                        insertEmoji(e.detail.emoji, 'name');
                    } else {
                        addEmoji(e.detail);
                    }

                });

                $(document).on('focusin', '#name,#message', function(e) {
                    isNameFocus = false;
                    if ($(e.target).hasClass('quill-editor')) {
                        isNameFocus = true;
                    }

                    if ($(e.target).attr('id') == 'name') {
                        isNameFocus = true;
                    }
                });
            });
        })();
    </script>
@endpush
