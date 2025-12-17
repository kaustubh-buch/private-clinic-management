@extends('clinic.layout.front')
@section('page-title', 'Profile')
@section('content')
    <div class="page-wrapper">
        <div class="page-inner-wrapper">
            <div class="profile-page-wrapper">
                <div class="profile-col large">
                    <form method="POST" action="{{ route('profile.update') }}" class="user_profile">
                        @csrf
                        <div class="white-card">
                            <h2>{{ __('messages.page_texts.your_details') }}</h2>
                            <div class="form-wrapper">
                                <div class="form-group half-width">
                                    <label for="first-name">First Name</label>
                                    <input type="text" value="{{ $user->first_name }}" class="form-control sm"
                                        id="first-name" name="first_name"
                                        maxlength="{{ config('constants.MAX_LENGTH.FIRST_NAME') }}">

                                </div>
                                <div class="form-group half-width">
                                    <label for="last-name">Last Name</label>
                                    <input type="text" value="{{ $user->last_name }}" class="form-control sm"
                                        id="last-name" name="last_name"
                                        maxlength="{{ config('constants.MAX_LENGTH.LAST_NAME') }}">

                                </div>
                            </div>
                        </div>
                        <div class="white-card">
                            <h2>Clinic Details</h2>
                            <div class="form-wrapper">
                                <div class="form-group half-width">
                                    <label for="clinic-name">Clinic Name</label>
                                    <input type="text" value="{{ $clinic->name }}" class="form-control sm" id="name"
                                        name="name">

                                </div>
                                <div class="form-group half-width">
                                    <label for="abn-num">ABN</label>
                                    <input type="text" value="{{ $user->company_abn }}" class="form-control sm input-number"
                                        id="abn-num" name="company_abn">

                                </div>
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control sm" id="address" name="address">{{ $clinic->address }}</textarea>

                                </div>
                                <div class="form-group half-width">
                                    <label for="contact-number">Contact number</label>
                                    <input type="text" value="{{ $clinic->contact_no }}" class="form-control sm input-number"
                                        id="contact-number" name="contact_no"
                                        maxlength="{{ config('constants.MAX_LENGTH.CONTACT') }}">

                                </div>
                                <div class="form-group half-width">
                                    <label for="state">{{ __('messages.labels.state') }}</label>
                                    <select class="form-control sm custom-select" name="state_id" id="state"
                                        data-placeholder="Select timezone">
                                        <option value="">Select state</option>
                                        @foreach ($states as $state)
                                            <option value="{{ $state->id }}"
                                                {{ $clinic && $clinic->state_id == $state->id ? 'selected' : '' }}>
                                                {{ $state->display_name }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>

                            </div>
                        </div>
                        <div class="white-card">
                            <h2>Preferences</h2>
                            <div class="form-wrapper">
                                <div class="form-group half-width">
                                    <div class="form-info-title">
                                        <label for="online-booking">Online Booking</label>
                                        <div class="switch small">
                                            <input type="hidden" name="is_online_booking" value="0">
                                            <input type="checkbox" name="is_online_booking" value="1"
                                                id="cb_online_booking_tooltip"
                                                {{ $clinic->is_online_booking ? 'checked' : '' }}>

                                            <span class="slider round"></span>
                                        </div>
                                        <div class="info-tooltip">
                                            <div class="tooltip-icon"><img src="{{ asset('front/images/info-icon.svg') }}"
                                                    alt="Reload Icon"></div>
                                            <div class="tooltip-text" id="div_online_booking_tooltip" style="width: 27.4rem !important;">
                                                <div class="tooltip-arrow"
                                                    data-arrow></div>
                                            </div>
                                        </div>
                                        <a href="javascript:void(0)"
                                            class="pl-2 {{ Str::contains($clinic->booking_link, config('services.shortio.domain')) ? 'd-none' : '' }}"
                                            id="btn-shorten-url">Shorten</a>


                                    </div>
                                    <input type="text" value="{{ $clinic->booking_link }}" class="form-control"
                                        id="online-booking" name="booking_link" placeholder="Paste your online booking link">

                                </div>
                                <div class="form-group average-checkup-input half-width">
                                    <div class="form-info-title">
                                        <label for="average-checkup">Average Check-Up Fee</label>
                                        <div class="info-tooltip">
                                            <div class="tooltip-icon" data-placement="top-end"><img
                                                    src="{{ asset('front/images/info-icon.svg') }}" alt="Reload Icon">
                                            </div>
                                            <div class="tooltip-text">Enter your clinic’s average check-up cost to ensure
                                                accurate revenue estimates on your dashboard.<div class="tooltip-arrow"
                                                    data-arrow></div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="text" value="{{ $clinic->average_checkup_fee }}"
                                        class="form-control" id="average-checkup" name="average_checkup_fee">


                                </div>
                                <div class="form-group">
                                    <div class="switch-info-wrapper">
                                        <div class="switch">
                                            <input type="hidden" name="six_month_recall_sms" value="0">
                                            <input type="checkbox" name="six_month_recall_sms" value="1"
                                                {{ $clinic->six_month_recall_sms ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                            <label class="switch-text">Clinical software sending automated 6-month recall
                                                SMS</label>
                                        </div>
                                        <div class="info-tooltip">
                                            <div class="tooltip-icon"><img
                                                    src="{{ asset('front/images/info-icon.svg') }}" alt="Reload Icon">
                                            </div>
                                            <div class="tooltip-text">If your clinic’s software automatically sends 6-month
                                                recall SMS reminders, Smile Orbit will account for this to ensure patients
                                                aren’t contacted too frequently.
                                                <div class="tooltip-arrow" data-arrow></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="checkbox-control-tooltip-wrapper">
                                        <div class="checkbox-control">
                                            <input type="checkbox" id="recieve-tips" name="receives_promotional_emails"
                                                {{ $user->receives_promotional_emails ? 'checked' : '' }}>
                                            <label for="recieve-tips">Receive tips, updates & exclusive offers</label>
                                        </div>
                                        <div class="info-tooltip">
                                            <div class="tooltip-icon"><img
                                                    src="{{ asset('front/images/info-icon.svg') }}" alt="Reload Icon">
                                            </div>
                                            <div class="tooltip-text">Get expert tips, feature updates, and exclusive
                                                offers to help you maximize Smile Orbit. You can enable or disable this
                                                anytime
                                                <div class="tooltip-arrow" data-arrow></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="white-card">
                            <button class="primary-btn small-btn w-auto">Save</button>
                        </div>
                    </form>
                </div>
                <div class="profile-col">
                    <div class="white-card">
                        <h2>Two-Factor Authentication</h2>
                        <p class="auth-change">{{'+'.config('constants.GLOBAL.DEFAULT_COUNTRY_CODE') .' '. CommonHelper::getFormattedMobileNo($clinic->mobile_no , 5) }} <a href="#"
                                class="change-link modal-btn primary-link" title="Change"
                                data-link="two-factor-authentication-modal">Change</a></p>
                    </div>
                    <div class="white-card">
                        <h2><a href="#" class="modal-btn" data-link="change-password-modal"
                                id="openChangePasswordModal">Change Password</a></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('clinic.modals.2fa-modal')
    @include('clinic.modals.change-password-modal')
@endsection
@push('scripts')
    <script>
        var resendCountdownTemplate = @json(__('messages.page_texts.resend_email_countdown'));
        var showChangePasswordModal = {{ $errors->has('current_password') || $errors->has('new_password') ? 1 : 0 }};
    </script>
    <script src="{{ asset('front/js/otp.js') }}"></script>
    <script src="{{ asset('front/js/countdown.js') }}"></script>
    <script>
        $(document).ready(function() {

            $('#contact-number').mask('0000 0000 0000', {
                placeholder: '____ ____ ____',
                onKeyPress: function(val, e, field, options) {
                    val = val.replace(/\D/g, '');
                    if (val.length <= 8) {
                        $(field).mask('0000 0000', options);
                    } else {
                        $(field).mask('0000 0000 0000', options);
                    }
                }
            });

            $('#abn-num').mask('00 000 000 000');

            $(".user_profile").validate({
                rules: {
                    first_name: {
                        required: true,
                        maxlength: {{ config('constants.MAX_LENGTH.FIRST_NAME') }},
                        NameValidation: true
                    },
                    last_name: {
                        required: true,
                        maxlength: {{ config('constants.MAX_LENGTH.LAST_NAME') }},
                        NameValidation: true
                    },
                    name: {
                        required: true,
                    },
                    company_abn: {
                        required: true,
                        normalizer: function(value) {
                            return value.replace(/\s/g, '');
                        },
                        minlength: {{ config('constants.MAX_LENGTH.COMPANY_ABN') }},
                        maxlength: {{ config('constants.MAX_LENGTH.COMPANY_ABN') }},
                        digits: true,
                    },
                    address: {
                        required: true,
                    },
                    contact_no: {
                        required: true,
                    },
                    state_id: {
                        required: true,
                    },
                    booking_link: {
                        required: function() {
                            return $('#cb_online_booking_tooltip').is(':checked');
                        },
                        url: true
                    },
                    average_checkup_fee: {
                        required: true,
                        number: true
                    }
                },
                messages: {
                    first_name: {
                        required: "{{ __('messages.validation.first_name_required') }}",
                        maxlength: "{{ __('messages.validation.first_name_max') }}"
                    },
                    last_name: {
                        required: "{{ __('messages.validation.last_name_required') }}",
                        maxlength: "{{ __('messages.validation.last_name_max') }}"
                    },
                    contact_no: {
                        required: "{{ __('messages.validation.contact_no_required') }}",
                    },
                    name: {
                        required: "{{ __('messages.validation.clinic_name_required') }}",
                    },
                    company_abn: {
                        required: "{{ __('messages.validation.company_abn_required') }}",
                        digits: "{{ __('messages.validation.company_abn_digits_only') }}",
                        minlength: "{{ __('messages.validation.company_abn_digits') }}",
                        maxlength: "{{ __('messages.validation.company_abn_digits') }}"
                    },
                    address: {
                        required: "{{ __('messages.validation.clinic_address_required') }}",
                    },
                    state_id: {
                        required: "{{ __('messages.validation.state_required') }}",
                    },
                    booking_link: {
                        required: "{{ __('messages.validation.online_booking_required') }}",
                        url: "Please enter a valid URL"
                    },
                    average_checkup_fee: {
                        required: "{{ __('messages.validation.average_check_up_fee_required') }}",
                        number: "Only numeric values are allowed"
                    }
                }
            });
            
            $('#online-booking').on('blur', function() {
                let bookingField = document.getElementById('online-booking');
                let val = bookingField.value.trim();

                if (val && !/^https?:\/\//i.test(val)) {
                    bookingField.value = 'https://' + val;
                    $(this).trigger('blur');
                }
            });

            @if ($errors->has('current_password') || $errors->has('new_password'))
                handleBackendValidationErrors('.change_password_modal', @json($errors->getMessages()));
            @elseif ($errors->any())
                handleBackendValidationErrors('.user_profile', @json($errors->getMessages()));
            @endif

            $(".change_password_modal").validate({
                onfocusout: false,
                onkeyup: false,
                onclick: false,
                focusInvalid: false,
                ignore: [],
                rules: {
                    current_password: {
                        required: true,
                    },
                    new_password: {
                        required: true,
                        strongPassword: true,
                        notEqualToCurrentPassword: true
                    },
                    new_password_confirmation: {
                        required: true,
                        equalTo: "#new_password"
                    }
                },
                messages: {
                    current_password: {
                        required: "{{ __('messages.validation.current_password_required') }}",
                    },
                    new_password: {
                        required: "{{ __('messages.validation.new_password_required') }}",
                        strongPassword: "{{ __('messages.validation.strong_password') }}",
                        notEqualToCurrentPassword:"{{ __('messages.validation.not_same_as_current_password') }}"
                    },
                    new_password_confirmation: {
                        required: "{{ __('messages.validation.confirm_password_required') }}",
                        equalTo: "{{ __('messages.validation.password_mismatch') }}"
                    }
                },
                submitHandler: function(form) {
                    let action = $(form).attr('action');
                    let method = $(form).attr('method');
                    const formData = $('.change-password-form').serializeArray();
                    const data = {};
                    formData.forEach(field => data[field.name] = field.value);
                    $.ajax({
                        url: action,
                        method: 'POST',
                        data: data,
                        success: function(response) {
                            if(response.success) {
                                showCustomToast(response.message, 'success', true);
                                closeModal('change-password-modal');
                            }
                        },
                        error: function(error) {
                            if(error.status == 422) {
                                var validator = $('.change-password-form').validate();
                                $.each(error.responseJSON.errors, function (field, message) {
                                    var errorObject = {};
                                    errorObject[field] = message[0];
                                    if(field == 'current_password') {
                                        $(`[name=${field}]`).val('');
                                    }
                                    validator.showErrors(errorObject);
                                });
                            }

                        }
                    });

                }
            });

            $('#openChangePasswordModal').on('click', function() {
                $('.change-password-form')[0].reset();
                $('.change-password-form').validate().resetForm();
            });

            // Close modal on clicking close button or backdrop
            $('.modal-close, .modal-backdrop').on('click', function() {
                $('.custom-modal.change-password-modal').fadeOut();
            });

            if (showChangePasswordModal) {
                validatePasswordStrength($('#new_password').val());
                $('#openChangePasswordModal').click();
            }


            let otpPhase = 'initial';

            function showStep(stepClass) {
                $('.step').addClass('d-none');
                $(`.${stepClass}`).removeClass('d-none');
            }

            function collectOtpValues(inputName) {
                let otp = '';
                $(`input[name="${inputName}[]"]`).each(function() {
                    otp += $(this).val();
                });
                return otp;
            }

            const otpValidations = {
                required: true,
                digits: true,
                minlength: 6,
                maxlength: 6
            };

            const otpMessages = {
                required: "{{ __('messages.validation.otp_required') }}",
                digits: "{{ __('messages.validation.otp_digits_only') }}",
                minlength: "{{ __('messages.validation.otp_length') }}",
                maxlength: "{{ __('messages.validation.otp_length') }}",
            };

            $('#otp-form').validate({
                ignore: '.otp-input',
                rules: {
                    otp: otpValidations,

                },
                messages: {
                    otp: otpMessages,

                },
                submitHandler: function() {

                    let url = (otpPhase === 'initial') ?
                        "{{ route('2fa.verify.otp') }}" :
                        "{{ route('profile.update.phone') }}";

                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            otp: $('#otp-value').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                $('.otp-input').val('');
                                $('#otp-value').val('');
                                if (otpPhase === 'initial') {
                                    showStep('step-new-phone');
                                } else {
                                    // Final update success, refresh or show message
                                    window.location.reload();
                                }
                            } else {
                                if(response.is_blocked) {
                                    showLoader();
                                    $('.logout-link').click();
                                } else {
                                    handleBackendValidationErrors('#otp-form',{otp:[otpPhase === 'initial' ? response.otp_error : response.message]})
                                }
                                // $('.otp-error-message').text(response.otp_error);
                            }
                        }
                    });
                }
            });

            $('#send-code-btn').on('click', function() {
                $.post("{{ route('2fa.send.code') }}", {
                    context: 'mobile_change'
                }, function() {
                    showCustomToast(commonWebsiteSettings.messages.otp_sent_success,'success', true);
                    otpPhase = 'initial';
                    showStep('step-otp');
                });
            });

            // Submit new phone number
            $('#phone-form').validate({
                rules: {
                    phone: {
                        required: true,
                        regex: getPatternFromString(commonWebsiteSettings.patterns.MOBILE_NO),
                    }
                },
                messages: {
                    phone: {
                        required: "{{ __('messages.validation.mobile_no_required') }}",
                        regex: "{{ __('messages.validation.mobile_no_invalid') }}",
                    },
                },
                submitHandler: function() {
                    $.ajax({
                        url: "{{ route('profile.request.update.phone') }}",
                        method: 'POST',
                        data: {
                            phone: $('#new-phn-number').val()
                        },
                        success: function() {
                            $('input[name="otp[]"]').val('');
                            $('.otp-submit-btn').text('Save');
                            otpPhase = 'phone_update';
                            $('.resend-code').data('context', 'mobile_change');
                            showStep('step-otp');
                            showCustomToast(commonWebsiteSettings.messages.otp_sent_success,'success', true);
                        },
                        error: function() {}
                    });
                }
            });
            
            // Optional: Auto-focus next input in OTP
            $(document).on('input', '.otp-input', function() {
                const $inputs = $('.otp-input');
                const index = $inputs.index(this);
                if (this.value.length === 1 && index < $inputs.length - 1) {
                    $inputs.eq(index + 1).focus();
                }
            });

            function resetModalStep() {
                $('.otp-submit-btn').text('Continue');
                $('.resend-code').data('context', 'mobile_old');
                showStep('step-initial');
                otpPhase = 'initial';
                $('.otp-input').val('');
                $('#otp-value').val('');
                $('#new-phn-number').val('');
                $('.error-message').text('');
            }

            $('.change-link').on('click', function() {
                resetModalStep();
            });


            function updateOnlineBookingTooltipText() {
                let value = $('#cb_online_booking_tooltip').is(':checked');
                let text = '{!! __('messages.tooltip.online_booking_off') !!}';
                if (value) {
                    text = '{{ __('messages.tooltip.online_booking_on') }}';
                }
                $('#div_online_booking_tooltip').html(text);
            }

            updateOnlineBookingTooltipText();
            $('#cb_online_booking_tooltip').on('change', updateOnlineBookingTooltipText);

            $('#new_password').on('input', function() {
                validatePasswordStrength(this.value);
            });

            $('#btn-shorten-url').on('click', function() {
                const originalUrl = $('#online-booking').val();

                if (!originalUrl) {
                    showCustomToast("{{ __('messages.validation.bookingurl_required') }}", 'error', true);
                    return;
                }

                $.ajax({
                    url: "{{ route('shorten.link.store') }}",
                    method: 'POST',
                    data: {
                        original_url: originalUrl
                    },
                    beforeSend: function() {
                        $('#btn-shorten-url').prop('disabled', true).text('Shortening...');
                    },
                    success: function(response) {

                        if (response.short_url) {
                            $('#btn-shorten-url').hide();
                            $('#online-booking').val(response.short_url);
                        } else {
                            $('#btn-shorten-url').prop('disabled', false).text("Shorten");
                            showCustomToast("{{ __('messages.alerts.shorten_url_error') }}",
                                'error', true);
                        }
                    },
                    error: function(xhr) {
                        $('#btn-shorten-url').prop('disabled', false).text("Shorten");
                        handleAjaxError(xhr, "{{ __('messages.alerts.shorten_url_error') }}");
                    }
                });
            });

        });
    </script>
@endpush
