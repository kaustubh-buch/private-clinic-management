<!doctype html>
<html lang="en">
@include('auth.includes.head')

<body>
    <div class="loader-overlay" id="pageLoader">
        <div class="loaderSpin"></div>
    </div>
    <div class="auth-wrapper d-flex align-items-center justify-content-center">
        <div class="auth-container w-100 d-flex align-items-center justify-content-center">
            <div class="row w-100">
                <div class="col left-col">
                    <img src="{{ asset('images/login-bg.jpg') }}" alt="Login Bg img" />

                </div>
                <div class="col right-col d-flex align-items-center justify-content-center">
                    <div class="auth-content">
                        <div class="logo">
                            <img src="{{ asset('images/logo.svg') }}" alt="Clinic Management" />
                        </div>
                        @yield('content')
                    </div>
                    <p class="auth-copy">Copyright © {{ date('Y') }} Clinic Management. All right reserved.</p>
                </div>
            </div>
        </div>
    </div>
    @include('auth.includes.scripts')
    @stack('scripts')
</body>
</html>