<header class="site-header">
    @php
        $cookieName = 'hide_blue_bar_until_' . Auth::user()->id;
    @endphp
    @if(!isset($_COOKIE[$cookieName]) || time() > $_COOKIE[$cookieName])
    @php
        $latestPatientUpdated = PatientService::latestPatientUpdatedAt(Auth::user());
        $headerTopClass = 'header-top-without-content';
        if((!$latestPatientUpdated || $latestPatientUpdated->diffInDays(now()) > 2)) {
            $headerTopClass = '';
        }
    @endphp
    <div class="header-top blue-bar-content {{ $headerTopClass }}">
        <div class="container">
        <div class="header-top-wrapper">
            @if (!$latestPatientUpdated || $latestPatientUpdated->diffInDays(now()) > 2)
                <div class="content-block">
                    <div class="content-wrapper">
                    <a href="javascript:void(0);" title="Reload"><img src="{{ asset('front/images/reload-icon.svg') }}" alt="Reload Icon"></a>
                        <p>
                            Upload the latest patient data to keep statistics accurate. 
                            @if($latestPatientUpdated)
                                <span>({{ $latestPatientUpdated->format('M d, Y') }})</span>
                            @endif
                        </p>
                    <a href="javascript:void(0);" title="Close" class="close-top-header"><img src="{{ asset('front/images/close-icon.svg') }}" alt="Close Icon"></a>
                    </div>
                </div>
            @endif
            @if (Auth::user()->activeSubscription && Auth::user()->activeSubscription->isFreePlan() == 1 )
                <div class="white-chip-wrapper">
                    <span>Free Trial: {{ Auth::user()->activeSubscription->remaining_days }} Days Left</span>
                </div>
            @endif

        </div>
        </div>
    </div>
    @endif
    <div class="header-bottom">
        <div class="logo-wrapper">
            <a href="{{ route('dashboard') }}" title="SmileOrbit">
                <img src="{{ asset('front/images/smile-orbit-logo.svg') }}" alt="Site Logo">
            </a>
        </div>
        <div class="bottom-right-block">
            <h1 class="header-title">@yield('page-title', 'Welcome back, ' . Auth::user()->company_name . ' 👋')</h1>
            <div class="button-wrapper">
                @php
                    $clinic = Auth::user()?->clinics;
                    $dedicatedNumber = $clinic?->dedicated_number_with_country_code;
                @endphp
                @if (!empty($dedicatedNumber))
                    <a href="javascript:void(0);" class="border-btn has-icon tooltip-wrap" style="cursor: default; pointer-events: none;"> <img src="{{ asset('front/images/call-icon.svg') }}" alt="Receiver"> {{ Auth::user()->clinics->dedicated_number_with_country_code }}
                        <div class="info-tooltip" style="cursor: pointer !important; pointer-events:visible !important">
                            <div class="tooltip-icon" data-placement="top-end"><img src="{{ asset('front/images/info-icon.svg') }}" alt="info-icon"></div>
                            <div class="tooltip-text">Your assigned clinic SMS number for patient communication.
                            <div class="tooltip-arrow" data-arrow></div></div>
                        </div>
                    </a>
                @endif

                @if (Auth::user()->clinics)
                    <span class="border-btn time-info">{{ CommonHelper::formatDate(Auth::user()->clinics->current_time,'g:i A') }}</span>
                @endif

                <a href="javascript:void(0);" title="Notification" @if($unReadNotifications->count())class="border-btn notification-icon has-notification-dot" @else class="border-btn notification-icon" @endif><img src="{{ asset('front/images/notification-icon.svg') }}" alt="notification"></a>
                <div class="hamburger">
                    <span></span>
                </div>
                <div class="notification-outer-wrapper">
                    <div class="notification-header-outer">
                        <div class="notification-header">
                            <div class="title">
                                <p>Notifications</p>
                                <span id="notificationCount">{{ $unReadNotifications->count() }}</span>
                            </div>
                            @if ($unReadNotifications->count())
                                <a id="markAllRead" href="javascript:void(0);" class="read-link primary-link">Mark all as read</a>
                            @endif
                        </div>
                        @if ($unReadNotifications->count())
                            <div class="notification-main-wrapper noty-wrap-item">
                                <ul>
                                    @foreach ($unReadNotifications as $notification)
                                        <li class="notification-item">
                                            <div class="icon-content-block">
                                                <div class="icon-wrapper green-bg">
                                                    <img src="{{ asset('front/images/'. $notification->image) }}" alt="Tick Icon">
                                                </div>
                                                <div class="content-wrapper">
                                                    <p><strong>{{ $notification->name }}</strong> </br>{{ $notification->message }}</p>
                                                    <span class="time">{{ $notification->created_at->format('h:i A') }} </span>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                    <!--  -->


                                </ul>
                            </div>
                        @else
                            <div class="mx-2 my-2">
                                No Notifications found.
                            </div>
                        @endif

                        <div class="notification-footer">
                            <a href="{{ route('notification.index') }}" title="View all" class="primary-link">View all</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
@include('clinic.modals.account-suspended-modal')
@if (Auth::user()->hasFailedSubscriptionPayment())
    @include('clinic.modals.payment-failed-modal')
@endif