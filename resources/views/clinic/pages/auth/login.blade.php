@extends('clinic.layout.auth')
@section('content')
    <div class="auth-right">
        <div class="form-block">
            <div class="title-block">
                <h1>{{ __('messages.page_texts.login_title') }}</h1>
                <p>{{ __('messages.page_texts.login_intro_front') }} </p>
            </div>
            <form action="{{ route('login.submit')}}" method="POST" id="loginForm">
                @csrf
                <div class="form-wrapper">
                    <div class="form-group">
                        <label for="email">{{ __('messages.labels.email_address') }} </label>
                        <input type="email" placeholder="{{ __('messages.placeholders.email_address') }}" class="form-control" id="email" name="email" value="{{ old('email') }}">

                    </div>
                    <div class="form-group">
                        <label for="password">{{ __('messages.labels.password') }}</label>
                        <input type="password" placeholder="{{ __('messages.placeholders.password') }}" class="form-control" id="password" name="password" maxlength="{{ config('constants.MAX_LENGTH.PASSWORD') }}">

                    </div>
                </div>
                <a href="{{ route('forgot-password') }}" title="{{ __('messages.page_texts.forgot_password')}}" class="forget-link primary-link forget-link-position">{{ __('messages.page_texts.forgot_password')}}</a>
                <button type="submit" class="primary-btn">{{ __('messages.button.login') }}</button>
            </form>
            <p class="message">{{ __('messages.page_texts.dont_have_account') }} <a href="{{ route('signup.step.one') }}" class="primary-link">{{ __('messages.page_texts.register_here') }}</a> </p>
        </div>
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
