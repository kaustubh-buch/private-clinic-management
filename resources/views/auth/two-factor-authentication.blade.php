@extends('auth.layout.main')
@section('content')
    <div class="authentication-content">
        <h2>{{ __('messages.page_texts.log_in_to_your_account')}}</h2>
        <p>{!! __('messages.page_texts.verification_code_pre')!!}</p>
        <form action="{{ route('2fa.verify.otp') }}" id="otpForm" method="POST">
            @csrf
            <div class="custom-form-group error">
                <label for="username">{{ __('messages.labels.code')}}</label>

                <div class="code-inputs">
                    @for ($i=1 ; $i<=6 ; $i++)
                        <input type="number" inputmode="numeric" maxlength="1" placeholder="-" class="otp-input" name="otp{{$i}}">
                    @endfor
                </div>
                <input type="hidden" name="otp" id="otp-value">
            </div>

            <p class="otp-error-message error-message">
                @if(session('otp_error'))
                    {{ session('otp_error') }}
                @enderror
            </p>

            <div class="btn-wrap">
                <button type="submit" class="btn btn-primary w-100" title="{{ __('messages.button.verify')}}">{{ __('messages.button.verify')}}</button>
                <p class="verify-link">{{ __('messages.page_texts.no_receive_email') }}
                    <a href="{{ route('2fa.resend') }}" id="resend-link">{{ __('messages.page_texts.resend_email') }}</a>
                </p>
                @if (session('secondsLeft') && session('secondsLeft') > 0)
                    <p class="error-message text-center" id="countdown-timer"></p>
                @endif
            </div>
        </form>
    </div>
@endsection
@push('scripts')
<script>
    var resendCountdownTemplate = '';
    @if (session('secondsLeft') && session('secondsLeft') > 0)
        var countdownTime = {{ (int) session('secondsLeft',0) }};
        resendCountdownTemplate = @json(__('messages.page_texts.resend_email_countdown'));
    @endif
</script>
<script src="{{ asset('js/countdown.js')}}"></script>
<script>
    $(document).ready(function() {
        // jQuery Validation
        $('#otpForm').validate({
            ignore:[],
            rules: {
                otp: {
                    required: true,
                    digits: true,
                    minlength: "{{ config('constants.MAX_LENGTH.OTP') }}",
                    maxlength: "{{ config('constants.MAX_LENGTH.OTP') }}"
                },

            },
            messages: {
                otp: {
                    required: "{{ __('messages.validation.otp_required') }}",
                    digits: "{{ __('messages.validation.otp_digits_only') }}",
                    minlength: "{{ __('messages.validation.otp_length') }}",
                    maxlength: "{{ __('messages.validation.otp_length') }}",
                },

            },

            errorPlacement: function(error, element) {
                if($(element).attr('name') == 'otp'){
                    error.insertAfter(element.parent());
                }
            },
            success: function(label,element) {
                if($(element).attr('name') == 'otp'){
                    $('.otp-error-message').html('');
                }
            },
            submitHandler: function (form) {
                showLoader();
                updateOtpValue();
                form.submit();
            }
        });
        $('#resend-link').on('click',function(){
            showLoader();
        });
    });
</script>
@if (session('secondsLeft') && session('secondsLeft') > 0)
<script>
    countdownTimer(false,{{ session('secondsLeft') }});
</script>
<script>
    $(document).ready(function(){
        @if(session()->has('otp_error'))
            const otp_error = @json(session('otp_error'));

            $('#otpForm').validate().showErrors({
                "otp": otp_error
            });
        @endif
    });
</script>

@endif
@endpush