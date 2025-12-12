@extends('auth.layout.main')
@section('content')
    <div class="forgot-pwd-content">
        <div class="d-flex">
            <a href="{{ route('login') }}" class="custom-link back-to-login-link" title="Back to Login">
                <img src="{{ asset('images/back-arrow.svg') }} " alt="Back arrow"> {{ __('messages.button.back') }}</a>
        </div>
        <h2>{{ __('messages.page_texts.reset_password_title') }}</h2>
        <p>{{ __('messages.page_texts.reset_password_intro') }}</p>
        <form action="{{ route('password.email') }}" method="POST" id="forgotPasswordForm">
            @csrf
            <div class="custom-form-group error">
                <label for="email">{{ __('messages.labels.email_address') }}</label>
                <input type="email" name="email" placeholder="{{ __('messages.placeholders.email_address') }}" id="email" name="email" value="{{ old('email') }}">
            </div>
            <div class="btn-wrap">
                <button type="submit" class="btn btn-primary w-100 forgot-psd-btn" title="{{ __('messages.button.send_reset_link') }}">{{ __('messages.button.send_reset_link') }}</button>
            </div>
            @if (old('email'))
                @if ($remainingCooldown > 0)
                    <p class="error-message text-center hidden" id="countdownMessage">You can request another email in <span id="countdown">0</span> seconds.</p>
                @endif
            @endif
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            const countdownMessage = $('#countdownMessage');
            const countdown = $('#countdown');
            const forgotBtn = $('.forgot-psd-btn');

            let countdownSeconds = {{ $remainingCooldown ?? 0 }};
            let timer = null;

            // Start countdown on page load
            if (countdownSeconds > 0) {
                startCountdown();
            }

            function startCountdown() {

                // Show countdown UI
                countdownMessage.removeClass('hidden');
                countdown.text(countdownSeconds);

                // Disable the button
                forgotBtn.prop('disabled', true);

                // Prevent multiple timers
                if (timer) clearInterval(timer);

                timer = setInterval(() => {
                    countdownSeconds--;
                    countdown.text(countdownSeconds);

                    if (countdownSeconds <= 0) {
                        clearInterval(timer);
                        timer = null;

                        // Re-enable button
                        forgotBtn.prop('disabled', false);

                        // Hide countdown UI
                        countdownMessage.addClass('hidden');
                    }
                }, 1000);
            }
            const forgotPasswordForm = $('#forgotPasswordForm').validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                },
                messages: {
                    email: {
                        required: "{{ __('messages.validation.email_required') }}",
                        email: "{{ __('messages.validation.email_invalid') }}"
                    },
                },
                submitHandler: function(form) {
                    if (isPageLoading()) {
                        return false;
                    }

                    // Block submit if cooldown still active
                    if (countdownSeconds > 0) {
                        startCountdown(); // refresh UI only
                        return false;
                    }
                    showLoader();
                    form.submit();
                }
            });
            @if ($errors->any())
                handleBackendValidationErrors('#forgotPasswordForm', @json($errors->getMessages()));
            @endif            
        });
    </script>
@endpush
