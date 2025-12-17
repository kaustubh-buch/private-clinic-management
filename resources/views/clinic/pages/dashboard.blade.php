@extends('clinic.layout.front')

@section('content')
    <div class="page-wrapper dashboard-wrapper">
        <div class="page-inner-wrapper">
            <div class="title-wrapper">
                <h2 class="mr-15 fw-600">{{ __('messages.page_texts.dashboard.title') }}</h2>
                <div class="button-wrapper">
                    <div class="btn-block d-flex gap-8">
                        <a href="#"
                            class="primary-btn small-btn modal-btn w-auto {{ !Auth::user()->activeSubscription ? 'gray-btn' : '' }}"
                            title="Update Patient Status" data-link="update-patient-modal"><em><img
                                    src="{{ asset('front/images/update-icon.svg') }}"
                                    alt="update-icon"></em>{{ __('messages.button.update_patient_status') }}</a>
                        <a href="{{ route('patients.import.index') }}"
                            class="primary-btn small-btn w-auto {{ $isClinicSuspended || !Auth::user()->activeSubscription || Auth::user()->hasLimitedAccessDueToFailedPayment() || Auth::user()->hasLimitedAccessDueToPendingPayment() ? 'gray-btn' : '' }}"
                            title="Import"><em><img src="{{ asset('front/images/import-icon.svg') }}"
                                    alt="import-icon"></em>{{ __('messages.button.import') }}</a>
                    </div>
                </div>
            </div>
            <div class="dashboard-grid-content-wrapper">
                <div class="dashboard-grid-item col-3">
                    <div class="card-block blue-card color-card">
                        <span>{{ __('messages.page_texts.dashboard.total_recall_revenue') }}</span>
                        <span class="count">{{ CommonHelper::formatPricing($totalRecallRevenue) }}</span>
                        <p>{{ __('messages.page_texts.dashboard.total_recall_revenue_subtitle') }}
                            {{ $clinicJoinedDate ?? '' }}</p>
                    </div>
                </div>
                <div class="dashboard-grid-item col-3">
                    <div class="card-block orange-card color-card">
                        <span>{{ __('messages.page_texts.dashboard.last_30_days_recall') }}</span>
                        <span class="count">{{ CommonHelper::formatPricing($last30DaysRecallRevenue) }}</span>
                        <p>{{ __('messages.page_texts.dashboard.last_30_days_recall_subtitle') }}</p>
                    </div>
                </div>
                <div class="dashboard-grid-item col-3">
                    <div class="card-block purple-card color-card">
                        <span>{{ __('messages.page_texts.dashboard.projected_additional_revenue') }}</span>
                        <span class="count">{{ CommonHelper::formatPricing($projectedAdditionalRevenue) }}</span>
                        <p>{{ __('messages.page_texts.dashboard.projected_additional_revenue_subtitle') }}</p>
                    </div>
                </div>
                <div class="dashboard-grid-item">
                    <div class="card-block white-card recall-follow-up-card">
                        @if (Auth::user()->clinics && Auth::user()->clinics->import->count() > 0)
                            <h2 class="fw-600">{{ $noOverdueForLastSixMonths ?? 0 }} {{ CommonHelper::pluralize('patient', $noOverdueForLastSixMonths ?? 0) }}
                                {{ __('messages.page_texts.dashboard.patients_overdue') }}:</h2>
                            <ul>
                                <li><span>{{ $neverReceivedSMS ?? 0 }} {{ CommonHelper::pluralize('patient', $neverReceivedSMS ?? 0) }}</span> {!! __('messages.page_texts.dashboard.never_received_sms') !!}</li>
                                <li><span>{{ $noOverdueForLastTwoMonths ?? 0 }} {{ CommonHelper::pluralize('patient', $noOverdueForLastTwoMonths ?? 0) }}</span> {!! __('messages.page_texts.dashboard.no_sms_two_months') !!}</li>

                            </ul>
                            <a href="{{ route('campaign.send') }}" class="primary-btn btn w-auto small-btn"><em><img
                                        src="{{ asset('front/images/send-msg-icon.svg') }}"
                                        alt="send-msg-icon"></em>{{ __('messages.button.send_sms') }}</a>
                        @else
                            <h2 class="fw-600" style="margin-bottom: 0">
                                {{ __('messages.page_texts.dashboard.lets_get_started') }}</h2>
                            <h4 class="fw-600" style="margin-bottom: 1.2rem">
                                {{ __('messages.page_texts.dashboard.import_and_see') }}</h4>
                            <ul>
                                <li><span></span> {{ __('messages.page_texts.dashboard.identify_unmessaged_patients') }}
                                </li>
                                <li><span></span> {{ __('messages.page_texts.dashboard.send_personalized_sms') }}</li>

                            </ul>
                            <a href="{{ route('patients.import.index') }}"
                                class="primary-btn btn w-auto small-btn {{ $isClinicSuspended || !Auth::user()->activeSubscription || Auth::user()->hasLimitedAccessDueToFailedPayment() || Auth::user()->hasLimitedAccessDueToPendingPayment() ? 'gray-btn' : '' }}"><em><img
                                        src="{{ asset('front/images/send-msg-icon.svg') }}"
                                        alt="send-msg-icon"></em>{{ __('messages.button.upload_patient_list') }}</a>
                        @endif

                    </div>
                </div>
                <div class="dashboard-grid-item recalls-on-demand-card-wrapper">
                    <div class="card-block recalls-on-demand-card">
                        <div class="title-block">
                            <h2 class="fw-600">{{ __('messages.page_texts.dashboard.recalls_on_demand_title') }}</h2>
                            <p>{!! __('messages.page_texts.dashboard.recalls_on_demand_description', ['countTxt' => ($top25Patients ?? 25) .' overdue '. CommonHelper::pluralize('patient', $top25Patients ?? 25)]) !!}
                            </p>
                        </div>
                        <div class="button-block d-flex justify-center items-center gap-5">
                            <a href="javascript:void(0);"
                                class="primary-btn white-btn w-auto small-btn modal-btn dash-recall-btn {{ $isClinicSuspended || Auth::user()->hasFailedSubscriptionPayment() || !Auth::user()->activeSubscription ? 'gray-btn' : '' }}"><em><svg
                                        width="14" height="14" viewBox="0 0 14 14" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M7.33512 13.6668C3.65322 13.6668 0.668457 10.682 0.668457 7.00016C0.668457 3.31826 3.65322 0.333496 7.33512 0.333496C11.017 0.333496 14.0018 3.31826 14.0018 7.00016C14.0018 10.682 11.017 13.6668 7.33512 13.6668ZM7.33512 12.3335C10.2807 12.3335 12.6685 9.9457 12.6685 7.00016C12.6685 4.05464 10.2807 1.66683 7.33512 1.66683C4.3896 1.66683 2.00179 4.05464 2.00179 7.00016C2.00179 9.9457 4.3896 12.3335 7.33512 12.3335ZM8.00179 7.00016H10.6685V8.3335H6.66846V3.66683H8.00179V7.00016Z"
                                            fill="#465DFF" />
                                    </svg></em>{{ __('messages.page_texts.dashboard.recalls_on_demand_button') }}</a>
                            <div class="info-tooltip">
                                <div class="tooltip-icon" @if (Auth::user()->clinics && Auth::user()->clinics->import->count() > 0)data-placement="top-end"@endif><img src="{{ asset('front/images/white-info-icon.svg') }}"
                                        alt="info-icon"></div>
                                <div class="tooltip-text" @if (!Auth::user()->clinics || Auth::user()->clinics->import->count() == 0)style="width: 243px !important;"@endif>
                                    @if (Auth::user()->clinics && Auth::user()->clinics->import->count() > 0)
                                        {{ __('messages.page_texts.dashboard.recalls_on_demand_tooltip') }}
                                    @else
                                        {!! __('messages.page_texts.dashboard.recalls_on_demand_import_tooltip') !!} @endif<div
                                        class="tooltip-arrow" data-arrow></div>
                                </div>
                            </div>
                        </div>
                        <p>
                        @if (Auth::user()->clinics && Auth::user()->clinics->import->count() > 0)
                            {{ __('messages.page_texts.dashboard.recalls_on_demand_disclaimer') }}
                        @else
                            {{ __('messages.page_texts.dashboard.recalls_on_demand_import_disclaimer') }}
                        @endif  
                        </p>
                    </div>
                </div>
                <div class="dashboard-grid-item upcoming-sms-card-wrapper">
                    <div class="card-block white-card upcoming-sms-card">
                        <div class="has-tooltip d-flex items-center gap-5 mb-2">
                            <h3 class="fw-600">{{ __('messages.page_texts.dashboard.upcoming_sms_title') }}</h3>
                            <div class="info-tooltip">
                                <div class="tooltip-icon"><img src="{{ asset('front/images/grey-info-icon.svg') }}"
                                        alt="info-icon"></div>
                                <div class="tooltip-text">{{ __('messages.page_texts.dashboard.upcoming_sms_tooltip') }}
                                    <div class="tooltip-arrow" data-arrow></div>
                                </div>
                            </div>
                        </div>
                        @if (isset($upcomingCampaigns) && $upcomingCampaigns->isNotEmpty())
                            <div class="upcoming-sms-list">
                                @foreach ($upcomingCampaigns as $upcomingCampaign)
                                    <div class="sms-block">
                                        <div class="date-block">
                                            <p>{{ CommonHelper::formatDate($upcomingCampaign->send_at, 'd') }}</p>
                                            <span>{{ CommonHelper::formatDate($upcomingCampaign->send_at, 'M') }}</span>
                                        </div>
                                        <div class="icon-block">
                                            <div class="icon"></div>
                                        </div>
                                        <div class="sms-detail">
                                            <p>{{ $upcomingCampaign->no_of_patients }}
                                                {{ $upcomingCampaign->campaign_type_id == 1 ? __('messages.page_texts.dashboard.personalised_recall') : __('messages.page_texts.dashboard.personalised_promotional') }}
                                            </p>
                                            <span>{{ CommonHelper::formatDate($upcomingCampaign->formatted_send_at, 'h:i A') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="no-upcoming-sms-list">
                                <p>{{ __('messages.page_texts.dashboard.no_upcoming_sms') }} <span
                                        class="fw-600">{{ __('messages.button.send_sms') }}</span>
                                    {{ __('messages.page_texts.dashboard.start_new_campaign') }}</p>
                            </div>
                        @endif


                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($showModal == true)
        @include('clinic.modals.secure-your-account-modal')
    @endif
    @include('clinic.modals.account-review-modal')
    @include('clinic.modals.update-patient-modal')
    @include('clinic.modals.recall-sms-confirmation-modal')
    @include('clinic.modals.no-eligible-patient-modal')
@endsection

@push('scripts')
    @if ($showModal == true)
        <script src="https://js.stripe.com/v3/"></script>
        <script src="{{ asset('front/js/otp.js') }}"></script>
        <script src="{{ asset('front/js/countdown.js') }}"></script>
        <script>
            var resendCountdownTemplate = @json(__('messages.page_texts.resend_email_countdown'));
        </script>
        <script>
            var stripeElementsReady = 0;

            function elementsReady() {
                stripeElementsReady++;
                if (stripeElementsReady == 3) {
                    hideLoader();
                }
            }

            $(document).ready(function() {
                const stripe = Stripe(@json(config('services.stripe.stripe_public_key')));
                var cardElements = initializeStripeElements(stripe, 'card-number', 'card-expiry', 'card-cvc');

                const modalId = "secure-your-accout-modal";
                // openDefaultModal(modalId);
                $('.secure-your-accout-modal .modal-content-wrapper').removeClass('hidden');

                const $form = $('#clinic-step-1-form');
                const $button = $('#step1-submit-btn');
                const $stepper = $('#secure-account-stepper');
                const $softwareSelect = $('#software_id');
                const fileTypes = "{{ $fileTypes }}";

                const $form2 = $('#identity-proof-form');

                let allowStepChange = false;


                const startStep = {{ $step ? $step : 0 }};
                const lockNavigation = true;
                let otpSentTo = null;
                let skipOtpValidation = false;


                $('#clinic-name').attr('maxlength', commonWebsiteSettings.max_length.CLINIC_NAME);
                $('#clinic-address').attr('maxlength', commonWebsiteSettings.max_length.ADDRESS);

                $.validator.addMethod('mobileMatchesSentCode', function(value, element) {
                    if (!otpSentTo) return true; // no code sent yet, allow
                    return value === otpSentTo;
                }, 'Mobile number does not match the one you requested OTP for.');

                $.validator.addMethod('otpRequiredBeforeContinue', function(value, element) {
                    if (typeof skipOtpValidation !== 'undefined' && skipOtpValidation) {
                        return true;
                    }
                    return $('.otp-block').is(':visible')
                }, "{{ __('messages.validation.mobile_otp_required') }}");

                $.validator.addMethod('requiresOtp', function(value, element) {
                    const mobileEntered = $.trim(value) !== '';
                    const otpVisible = $('#otp-wrapper').is(':visible');

                    // If mobile is entered, OTP wrapper must be visible
                    return !mobileEntered || otpVisible;
                }, 'Please complete the OTP verification.');
                $stepper.find('.sw-btn-next, .sw-btn-prev').hide();
                $stepper.smartWizard("setOptions", {
                    keyboard: false,
                    enableUrlHash: false,
                    anchor: {
                        enableDoneStateNavigation: false // Enable/Disable the done state navigation
                    },
                });

                $stepper.on("leaveStep", function() {
                    if (allowStepChange) {
                        allowStepChange = false; // reset after use
                        return true;
                    }
                    return false;
                });
                setTimeout(() => {
                    allowStepChange = true;
                    $stepper.smartWizard("goToStep", startStep);
                }, 0);


                $('.state-dropdown').select2({
                    minimumResultsForSearch: -1,
                    dropdownParent: jQuery('body'),
                });;

                $form.validate({
                    ignore: ".otp-input",
                    rules: {
                        name: {
                            required: true,
                            maxlength: {{ config('constants.MAX_LENGTH.CLINIC_NAME') }},
                        },
                        address: {
                            required: true,
                            maxlength: {{ config('constants.MAX_LENGTH.ADDRESS') }},
                        },
                        software_id: {
                            required: true,
                        },
                        other_software: {
                            required: function() {
                                return $('#software_id').val() === 'other';
                            },
                        },
                        contact_no: {
                            required: function() {
                                return $('#software_id').val() !== 'other';
                            },
                            regex: getPatternFromString(commonWebsiteSettings.patterns.CONTACT_NO),
                        },
                        mobile_no: {
                            required: {
                                depends: function() {
                                    return $('#software_id').val() !== 'other';
                                }
                            },
                            regex: {
                                depends: function() {
                                    return $('#software_id').val() !== 'other';
                                },
                                param: getPatternFromString(commonWebsiteSettings.patterns.MOBILE_NO)
                            },
                            mobileMatchesSentCode: {
                                depends: function() {
                                    return $('#software_id').val() !== 'other';
                                }
                            },
                            otpRequiredBeforeContinue: {
                                depends: function() {
                                    return $('#software_id').val() !== 'other' && !$('.otp-wrapper').is(
                                        ':visible');
                                }
                            }
                        },
                        state_id: {
                            required: function() {
                                return $('#software_id').val() !== 'other';
                            },
                        },
                        otp: {
                            required: function() {
                                return $('#software_id').val() !== 'other' && $('.otp-block').is(
                                    ':visible');
                            },
                            digits: true,
                            minlength: {{ config('constants.MAX_LENGTH.OTP') }},
                            maxlength: {{ config('constants.MAX_LENGTH.OTP') }}
                        }

                    },
                    onfocusout: function(element) {
                        if ($(element).hasClass("is-invalid")) {
                            this.element(element);
                        }
                    },
                    onkeyup: false,
                    messages: {
                        name: {
                            required: "{{ __('messages.validation.clinic_name_required') }}",
                            maxlength: "{{ __('messages.validation.clinic_name_max', ['maxlength' => config('constants.MAX_LENGTH.CLINIC_NAME')]) }}"
                        },
                        address: {
                            required: "{{ __('messages.validation.clinic_address_required') }}",
                            maxlength: "{{ __('messages.validation.address_max', ['maxlength' => config('constants.MAX_LENGTH.ADDRESS')]) }}"
                        },
                        software_id: "{{ __('messages.validation.software_id_required') }}",
                        other_software: {
                            required: "{{ __('messages.validation.other_software_required') }}",
                        },
                        contact_no: {
                            required: "{{ __('messages.validation.contact_no_required') }}",
                            regex: "{{ __('messages.validation.contact_no_invalid') }}",
                        },
                        mobile_no: {
                            required: "{{ __('messages.validation.mobile_no_required') }}",
                            regex: "{{ __('messages.validation.mobile_no_invalid') }}",
                            mobileMatchesSentCode: 'Mobile number has changed. Please request a new OTP.',
                            otpRequiredBeforeContinue: "{{ __('messages.validation.mobile_otp_required') }}"
                        },
                        state_id: "{{ __('messages.validation.state_required') }}",
                        otp: {
                            required: commonWebsiteSettings.messages.otp_required,
                            digits: commonWebsiteSettings.messages.otp_digit_only,
                            minlength: commonWebsiteSettings.messages.otp_length,
                            maxlength: commonWebsiteSettings.messages.otp_length
                        }
                    },
                    submitHandler: function(form) {
                        return false;
                    }
                });


                function toggleSoftwareUI() {
                    const isOther = $softwareSelect.val() === 'other';
                    $('#other_software_group').toggle(isOther);
                    $('#contact_number_group, #mobile_number_group, #state_group,#unsupported_software_text').toggle(
                        !isOther);
                    $('#unsupported_software_text').toggle(isOther);
                    $button.text(isOther ? "{{ __('messages.button.notify_me_when_available') }}" :
                        "{{ __('messages.labels.continue') }}");
                    fixWizardHeightDebounced($stepper);

                    if (isOther) {
                        $('#mobile-number').val('');
                        $('#mobile-number').closest('.form-group').find('.error-message').html('');
                        $('.otp-block').addClass('d-none');
                        $('.send-mobile-code').addClass('disabled').css('pointer-events', 'none').css('opacity', '0.5');
                    } else {
                        if ($('#mobile-number').val() != '') {
                            toggleSendCodeLink();
                        } else {
                            $('.send-mobile-code').addClass('disabled').css('pointer-events', 'none').css('opacity',
                                '0.5');
                        }
                    }

                }

                function handleStepLogic(stepIndex, onSuccess) {
                    if (stepIndex === 0) {

                        const $form = $('#clinic-step-1-form');


                        // if($('#software_id').val() != 'other'){
                        //     const otpWrapperVisible = $('.otp-block').is(':visible');

                        //     if(!otpWrapperVisible){
                        //         $('.otp-block').removeClass('d-none');
                        //     }
                        // }
                        if (!$form.valid()) {
                            fixWizardHeightDebounced($stepper);
                            return false;
                        }


                        $.ajax({
                            url: clinicStoreUrl,
                            type: 'POST',
                            data: $form.serialize(),
                            success(response) {
                                onSuccess(true); // continue navigation after AJAX
                            },
                            error(xhr) {
                                if (xhr.status === 422) {
                                    displayServerSideError(xhr.responseJSON.errors);
                                    fixWizardHeightDebounced($stepper);
                                }
                                onSuccess(false);
                            }
                        });

                        return false;
                    } else if (stepIndex == 1) {

                        const $form = $('#payment-method-step-2-form');
                        if (!$form.valid()) {
                            fixWizardHeightDebounced($stepper);
                            return false;
                        }

                        showLoader();

                        var cardName = $('#card-holder-name').val();

                        stripe.createPaymentMethod({
                            type: 'card',
                            card: cardElements['cardNumber'],
                            billing_details: {
                                name: cardName,
                            },
                        }).then(function(result) {
                            if (result.error) {
                                $('#cardNumberError').text('');
                                $('#cardExpiryError').text('');
                                $('#cardCVVError').text('');
                                console.log(result.error.code);
                                switch (result.error.code) {
                                    case 'invalid_number':
                                    case 'incomplete_number':
                                        $('#cardNumberError').text(
                                            "{{ __('messages.validation.card_number_error') }}");
                                        break;
                                    case 'invalid_expiry_month':
                                    case 'incomplete_expiry':
                                    case 'invalid_expiry_year':
                                        $('#cardExpiryError').text(
                                            "{{ __('messages.validation.incomplete_expiry_error') }}");
                                        break;
                                    case 'invalid_expiry_month_past':
                                    case 'invalid_expiry_year_past':
                                        $('#cardExpiryError').text(
                                            " {{ __('messages.validation.card_expire_error') }}");
                                        break;
                                    case 'invalid_cvc':
                                    case 'incomplete_cvc':
                                        $('#cardCVVError').text(
                                            "{{ __('messages.validation.card_cvv_error') }}");
                                        break;
                                    default:
                                        showCustomToast(result.error.message, 'error', true);
                                }
                                hideLoader();
                            } else {
                                addCard(result, onSuccess);
                            }
                        });
                    } else if (stepIndex == 2) {

                        const $form = $('#identity-proof-form');
                        if (!$form.valid()) {
                            fixWizardHeightDebounced($stepper);
                            return false;
                        }
                        const formData = new FormData($form[0]);

                        $.ajax({
                            url: "{{ route('drivinglicense.store') }}", // Your backend endpoint
                            type: 'POST',
                            data: formData,
                            processData: false, // Important!
                            contentType: false, // Important!
                            success: function(response) {
                                if(response.success){
                                    $('.dashboard-wrapper').hide();
                                    closeModal(modalId);
                                    openDefaultModal('account-pending-review');
                                    setTimeout(() => {
                                        $('.dashboard-wrapper').show();
                                    }, 300);
                                }

                            },
                            error: function(error) {
                                if (xhr.status === 422) {
                                    let errors = xhr.responseJSON.errors;

                                    if (errors) {

                                        if (errors.front) {
                                            let error_html =
                                                `<span class='error-message'>${ errors.front}</span>`;
                                            error_html.insertAfter('#front-box');
                                        } else if (errors.back) {
                                            let error_html =
                                                `<span class='error-message'>${ errors.front}</span>`;
                                            error_html.insertAfter('#back-box');
                                        }
                                        fixWizardHeightDebounced($stepper);
                                    }
                                }
                            }
                        })
                        //onSuccess(false);
                    }

                    // Future logic for step 1, 2, etc.
                    return true; // allow step move
                }

                $('.send-mobile-code').on('click', function() {
                    const $sendLink = $(this);
                    const mobile = $('input[name="mobile_no"]').val();
                    skipOtpValidation = true;
                    const isMobileValid = $('input[name="mobile_no"]').valid();
                    skipOtpValidation = false;
                    if (isMobileValid) {
                        $.post('/send-code', {
                            mobile
                        }, function(response) {
                            $('.otp-block').removeClass('d-none');
                            fixWizardHeightDebounced($('#secure-account-stepper'));
                            $sendLink.addClass('d-none');
                            otpSentTo = mobile;
                            $('.resend-ajax-link').removeClass('d-none');
                        });
                    }

                });

                function toggleSendCodeLink() {
                    const $link = $('.send-mobile-code');
                    const value = $('#mobile-number').val().trim();
                    const pattern = getPatternFromString(commonWebsiteSettings.patterns.MOBILE_NO);
                    skipOtpValidation = true;
                    const isMobileValid = pattern.test(value);
                    skipOtpValidation = false;
                    if (isMobileValid) {
                        $link.removeClass('disabled').css('pointer-events', 'auto').css('opacity', '1');
                    } else {
                        $link.addClass('disabled').css('pointer-events', 'none').css('opacity', '0.5');
                    }
                }

                $('#mobile-number').on('change keyup', function() {
                    const current = $(this).val();

                    if (otpSentTo && current !== otpSentTo) {
                        $('.otp-block').addClass('d-none');
                        $('.otp-input').val(''); // Clear OTP inputs
                        $('.send-mobile-code').removeClass('d-none');
                        $('.resend-ajax-link').addClass('d-none');
                        fixWizardHeightDebounced($('#secure-account-stepper'));
                        otpSentTo = null;
                    }

                    toggleSendCodeLink();
                });

                $('select').on('change', function() {
                    $(this).valid();
                });


                if (startStep == 0) {
                    toggleSoftwareUI();
                }


                $('.secure-modal-next-btn').on('click', function() {
                    const stepInfo = $('#secure-account-stepper').smartWizard('getStepInfo');
                    const currentStepIndex = stepInfo.currentStep;

                    handleStepLogic(currentStepIndex, function(success) {
                        allowStepChange = true;
                        if (success) {
                            if (currentStepIndex == 0 && $softwareSelect.val() == 'other') {
                                setTimeout(() => {
                                    $('.logout-link').click();
                                }, 200);
                                return;
                            }

                            $stepper.smartWizard("next");
                        }
                        hideLoader();


                    });
                });

                $softwareSelect.on('change', toggleSoftwareUI);


                $('#payment-method-step-2-form').validate({
                    rules: {
                        name: {
                            required: true,
                        },
                    },
                    messages: {
                        name: "{{ __('messages.validation.card_holder_name_required') }}"
                    },
                    highlight: function(element) {
                        fixWizardHeightDebounced($('#secure-account-stepper'));
                    },
                    unhighlight: function(element) {
                        fixWizardHeightDebounced($('#secure-account-stepper'));

                    },
                    submitHandler: function(form) {
                        return false;
                    }
                });

                function addCard(stripeResponse, onSuccess) {
                    var formData = {
                        'stripe_pm_id': stripeResponse.paymentMethod.id,
                        'card_last_digits': stripeResponse.paymentMethod.card.last4,
                        'card_expiry_month': stripeResponse.paymentMethod.card.exp_month,
                        'card_expiry_year': stripeResponse.paymentMethod.card.exp_year,
                        'card_brand': stripeResponse.paymentMethod.card.brand,
                        'name': stripeResponse.paymentMethod.billing_details.name
                    };

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('paymentmethod.store') }}",
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                onSuccess(true);
                            } else {
                                showCustomToast(response.message, 'error', true);
                                onSuccess(false);
                            }
                            hideLoader();

                        },
                        error: function(xhr, status, error) {
                            handleAjaxError(xhr);
                            $('#cardNumberError').text('');
                            $('#cardExpiryError').text('');
                            $('#cardCVVError').text('');
                            onSuccess(false);
                            hideLoader();
                        }
                    });
                }

                function setupFileInputSync(inputId, hiddenId, box) {
                    const input = document.getElementById(inputId);
                    const hidden = document.getElementById(hiddenId);
                    const handleFiles = (files) => {
                        const dataTransfer = new DataTransfer();
                        Array.from(files).forEach(file => dataTransfer.items.add(file));
                        hidden.files = dataTransfer.files;

                        if ($(hidden).hasClass('error-message')) {
                            $(hidden).valid();
                        }

                        fixWizardHeightDebounced($stepper);
                    };

                    document.getElementById(inputId)?.addEventListener('change', () => {
                        handleFiles(input.files);
                    });

                    document.getElementById(box)?.addEventListener('drop', () => {
                        handleFiles(input.files);
                    });

                }

                setupFileInputSync('front-input', 'front-hidden', 'front-box');
                setupFileInputSync('back-input', 'back-hidden', 'back-box');

                $form2.validate({
                    ignore: [],
                    rules: {
                        front: {
                            required: true,
                            accept: fileTypes
                        },
                        back: {
                            required: true,
                            accept: fileTypes

                        }
                    },
                    errorClass: 'error-message',
                    messages: {
                        front: {
                            required: "{!! __('messages.validation.driving_license.front_required') !!}",
                            accept: "{{ __('messages.validation.driving_license.accept', ['fileTypes' => $fileTypes]) }}"
                        },
                        back: {
                            required: "{!! __('messages.validation.driving_license.back_required') !!}",
                            accept: "{{ __('messages.validation.driving_license.accept', ['fileTypes' => $fileTypes]) }}"
                        }
                    },
                    errorPlacement: function(error, element) {
                        const placementMap = {
                            front: '#front-box',
                            back: '#back-box'
                        };
                        error.insertAfter(placementMap[element.attr("name")] || element);
                    },

                });

            });
        </script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const isInstantModal = @json($isInstantModal);
            const dashboard = document.querySelector('.dashboard-wrapper');
            const secureModal = document.querySelector('.secure-your-accout-modal');
            const reviewModal = document.querySelector('.account-review-modal');
            const modal = secureModal?.classList.contains('visible') ? secureModal : (reviewModal?.classList
                .contains('visible') ? reviewModal : null);
            if (dashboard) {
                if (isInstantModal) {
                    if (modal) {
                        setTimeout(() => {
                            dashboard.style.display = 'block';
                        }, 300);
                    } else {
                        dashboard.style.display = 'block';
                    }
                } else {
                    dashboard.style.display = 'block';
                }
            }
        });
        $(function() {

            let recallCount = "{{ $top25Patients }}";
            $('.recall-count').text(recallCount ? recallCount : 25);

            $('.dash-recall-btn').on('click', function(e) {
                e.preventDefault();
                if (recallCount == 0) {
                    openDefaultModal('no-eligible-patients');
                } else {
                    openDefaultModal('recall-sms-confirmation');
                }
            });
            var patientsSearchUrl = "{{ route('patients.search') }}?is_dashboard=1";

            $('.patient-name-search').typeahead({
                minLength: {{ config('constants.PATIENT.TYPEAHEAD.MIN_LENGTH') }},
                delay: {{ config('constants.PATIENT.TYPEAHEAD.DELAY') }},
                limit: {{ config('constants.PATIENT.TYPEAHEAD.LIMIT') }},

                source: function(query, process) {
                    return $.ajax({
                        url: patientsSearchUrl,
                        type: 'GET',
                        data: {
                            query: query
                        },
                        showLoader: false,
                        success: function(data) {
                            return process(data);
                        }
                    });
                },
                displayText: function(item) {
                    return item.text;
                },
                sorter: function(items) {
                    return items;
                },
                afterSelect: function(item) {
                    $('#update-patient-search').val(item.id);
                    $('#update-patient-search').valid();
                },
            });

            $('.patient-name-search').on('input', function() {
                $('#update-patient-search').val('');
            });

            $('#update-patient-status').on('change', function() {
                $('.next-recall-confirmation-block').toggle($(this).val() == 'COMPLETE');
            });

            $('#patient-status-form').validate({
                ignore: ":hidden:not(#update-patient-search)",
                rules: {
                    status: {
                        required: true
                    },
                    patient_id: {
                        required: true
                    },
                    booked_next_recall: {
                        required: function() {
                            return $('#update-patient-status').val() === 'COMPLETE';
                        }
                    }
                },
                messages: {
                    status: "{{ __('messages.validation.update_patient_status.status_required') }}",
                    patient_id: "{{ __('messages.validation.update_patient_status.patient_required') }}",
                    booked_next_recall: "{{ __('messages.validation.update_patient_status.next_recall_required') }}"
                },
                submitHandler: function(form, e) {
                    e.preventDefault();
                    const patientId = $('[name=patient_id]').val();
                    const action = $('#update-patient-status').val();
                    var bookedNextRecall = $('input[name=booked_next_recall]:checked').val();
                    $.ajax({
                        url: "{{ route('patients.status.action') }}",
                        type: 'POST',
                        data: {
                            patient_id: patientId,
                            action: action,
                            bookedNextRecall: bookedNextRecall
                        },
                        success: function(response) {
                            window.location.reload();
                        },
                        error: function(xhr) {
                            handleAjaxError(xhr);
                        }
                    });
                }
            });

            $('#patient-status-form').on('change input', 'select, input', function() {
                $(this).valid();
            });

            $('#sendQuickRecallBtn').on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route('dashboard.quick-recall.send') }}',
                    method: 'POST',
                    success: function(response) {
                        closeModal('recall-sms-confirmation');
                        if (response.success) {
                            $("body,html").removeClass('modal-open');
                            showCustomToast(`Recalls on demand sent successfully.`,'success',true);
                            setTimeout(function() {
                                window.location.reload();
                            }, 300);
                        } else {
                            //TODO: Currently put as toastr once new design finallize then replace with popup.
                            switch (response.error) {
                                case 'NO_PATIENT_FOUND':
                                    openDefaultModal('no-eligible-patients');
                                    break;
                                case 'EXCEED_SEGMENTS':
                                    showCustomToast(
                                        "{{ __('messages.alerts.used_all_credits') }}",
                                        'error', true);
                                    break;
                                case 'FAILED_PAYMENT':
                                    showCustomToast(
                                        "{{ __('messages.alerts.failed_payment') }}",
                                        'error', true);
                                    break;
                                default:
                                    showCustomToast(
                                        "{{ __('messages.global.something_went_wrong') }}",
                                        'error', true);
                            }
                        }
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr);
                    }
                });
            });
        });
    </script>
@endpush
