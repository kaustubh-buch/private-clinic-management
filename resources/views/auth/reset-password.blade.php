@extends('auth.layout.main')
@section('content')
<div class="newpassword-content">
    <h2>{{ __('messages.page_texts.create_password_title') }}</h2>
    <p>{{ __('messages.page_texts.create_password_intro') }}</p>
    <form action="{{ route('password.update')}}" method="POST" id="resetPasswordForm">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}" />
        <input type="hidden" name="token" value="{{ $token }}" />
        <div class="custom-form-group error">
            <label for="username">{{ __('messages.labels.new_password') }}</label>
            <input type="password" placeholder="{{ __('messages.placeholders.new_password') }}" id="newPassword" name="password" maxlength="{{ config('constants.MAX_LENGTH.PASSWORD') }}" />
            <div class="status-box hidden" id="passwordSuggestions">
                <p id="strength-text" class="weak">Password is WEAK!</p>
                <div class="strength-bar">
                    <span class="bar bar1"></span>
                    <span class="bar bar2"></span>
                    <span class="bar bar3"></span>
                    <span class="bar bar4"></span>
                    <span class="bar bar5"></span>
                </div>
                <span class="min-text">{{ __('messages.labels.password_requirements_title') }}</span>
                <div class="requirements">
                    <p>
                    <span class="icon len"
                        ><img src="{{ asset('images/close-icon.svg') }}" alt="Back arrow" />
                    </span>
                    {{ __('messages.password_requirements.min_length') }}
                    </p>
                    <p>
                    <span class="icon upper"
                        ><img src="{{ asset('images/close-icon.svg') }}" alt="Back arrow"
                        /></span>
                    {{ __('messages.password_requirements.uppercase') }}
                    </p>
                    <p>
                    <span class="icon num"
                        ><img src="{{ asset('images/close-icon.svg') }}" alt="Back arrow"
                        /></span>
                    {{ __('messages.password_requirements.number') }}
                    </p>
                    <p>
                    <span class="icon special"
                        ><img src="{{ asset('images/close-icon.svg') }}" alt="Back arrow"
                        /></span>
                    {{ __('messages.password_requirements.special_char') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="custom-form-group">
            <label for="password">{{ __('messages.labels.confirm_password') }}</label>
            <div class="input-password-wrap">
                <input type="password" placeholder="{{ __('messages.placeholders.reenter_password') }}" id="confirmPassword" name="password_confirmation" maxlength="{{ config('constants.MAX_LENGTH.PASSWORD') }}" />
            </div>
        </div>
        <div class="btn-wrap">
            <button type="submit" class="btn btn-primary w-100" title="{{ __('messages.button.password_save') }}">{{ __('messages.button.password_save') }}</button>
        </div>
    </form>
</div>

@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            @if ($errors->any())
                handleBackendValidationErrors('#loginForm', @json($errors->getMessages()));
            @endif
            $('#resetPasswordForm').validate({
                rules: {
                    password: {
                        required: true,
                        strongPassword: true
                    },
                    password_confirmation: {
                        required: true,
                        equalTo: '#newPassword'
                    }
                },
                messages: {
                    password: {
                        required: "{{ __('messages.validation.password_required') }}",
                        strongPassword: "{{ __('messages.validation.strong_password') }}",
                    },
                    password_confirmation: {
                        required: "{{ __('messages.validation.confirm_password_required') }}",
                        equalTo: "{{ __('messages.validation.password_mismatch') }}"
                    },
                    email: {
                        required: "{{ __('messages.validation.email_required') }}",
                        email: "{{ __('messages.validation.email_invalid') }}"
                    },
                },
                submitHandler: function(form) {
                    if (isPageLoading()) {
                        return false;
                    }
                    showLoader();
                    form.submit();
                }

            });

            $('#newPassword').on('input', function() {
                validatePasswordStrength(this.value);
                $(this).valid();
            });
        });
    </script>

@endpush
