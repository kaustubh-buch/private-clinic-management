@extends('auth.layout.main')
@section('content')
    <div class="login-content">
        <h2>{{ __('messages.page_texts.login_title') }}</h2>
        <p>{{ __('messages.page_texts.login_intro_front') }}</p>
        <form action="{{ route('login.submit')}}" method="POST" id="loginForm">
            @csrf
            <div class="custom-form-group error">
                <label for="username">{{ __('messages.labels.email_address') }}</label>
                <input type="email" placeholder="{{ __('messages.placeholders.email_address') }}" id="email" name="email" value="{{ old('email') }}">
            </div>
            <div class="custom-form-group">
                <label for="password">{{ __('messages.labels.password') }}</label>
                <div class="input-password-wrap">
                    <input type="password" placeholder="{{ __('messages.placeholders.password') }}" id="password" name="password">
                    <div class="pwd-icon-wrap">
                        <img class="eye-on" src="{{ asset('images/eye-on.svg') }}" alt="Eye On" />
                        <img class="eye-off" src="{{ asset('images/eye-off.svg') }}" alt="Eye Off" />
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <a href="{{ route('forgot-password') }}" class="custom-link forgot-pwd-link" title="{{ __('messages.page_texts.forgot_password')}}">{{ __('messages.page_texts.forgot_password')}}</a>
            </div>
            <div class="btn-wrap">
                <button type="submit" class="btn btn-primary w-100" title="{{ __('messages.button.login') }}">{{ __('messages.button.login') }}</button>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        const loginForm = $('#loginForm').validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                }
            },
            messages: {
                email: {
                    required: "{{ __('messages.validation.email_required') }}",
                    email: "{{ __('messages.validation.email_invalid') }}"
                },
                password: {
                    required: "{{ __('messages.validation.login_password_required') }}",
                }
            },
            submitHandler: function (form) {
                if (isPageLoading()) {
                    return false;
                }
                showLoader();
                form.submit();
            }
        });

        @if ($errors->any())
            handleBackendValidationErrors('#loginForm', @json($errors->getMessages()));
        @endif

        @if(session()->has('login_error'))
            const login_error = @json(session('login_error'));

            loginForm.showErrors({
                "password": login_error
            });
        @endif
    });
</script>
@endpush