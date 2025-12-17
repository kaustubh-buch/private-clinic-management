<div class="sidebar-outer-wrapper">
    <div class="sidebar-nav-wrapper">
        <ul class="sidebar-menu-wrapper">
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M9.66667 15.5C9.20642 15.5 8.83333 15.1269 8.83333 14.6667V8C8.83333 7.53975 9.20642 7.16667 9.66667 7.16667H14.6667C15.1269 7.16667 15.5 7.53975 15.5 8V14.6667C15.5 15.1269 15.1269 15.5 14.6667 15.5H9.66667ZM1.33333 8.83333C0.8731 8.83333 0.5 8.46025 0.5 8V1.33333C0.5 0.8731 0.8731 0.5 1.33333 0.5H6.33333C6.79358 0.5 7.16667 0.8731 7.16667 1.33333V8C7.16667 8.46025 6.79358 8.83333 6.33333 8.83333H1.33333ZM5.5 7.16667V2.16667H2.16667V7.16667H5.5ZM1.33333 15.5C0.8731 15.5 0.5 15.1269 0.5 14.6667V11.3333C0.5 10.8731 0.8731 10.5 1.33333 10.5H6.33333C6.79358 10.5 7.16667 10.8731 7.16667 11.3333V14.6667C7.16667 15.1269 6.79358 15.5 6.33333 15.5H1.33333ZM2.16667 13.8333H5.5V12.1667H2.16667V13.8333ZM10.5 13.8333H13.8333V8.83333H10.5V13.8333ZM8.83333 1.33333C8.83333 0.8731 9.20642 0.5 9.66667 0.5H14.6667C15.1269 0.5 15.5 0.8731 15.5 1.33333V4.66667C15.5 5.1269 15.1269 5.5 14.6667 5.5H9.66667C9.20642 5.5 8.83333 5.1269 8.83333 4.66667V1.33333ZM10.5 2.16667V3.83333H13.8333V2.16667H10.5Z"
                            fill="#747985" />
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li
                class="{{ request()->routeIs('inbox.*') ? 'active page-active' : '' }} {{ Auth::user()->hasLimitedAccessDueToFailedPayment() || Auth::user()->hasLimitedAccessDueToPendingPayment() || !Auth::user()->activeSubscription ? 'disabled' : '' }}">
                <a href="{{ route('inbox.index') }}">
                    <svg width="18" height="16" viewBox="0 0 18 16" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M1.49984 0.5H16.4998C16.9601 0.5 17.3332 0.8731 17.3332 1.33333V14.6667C17.3332 15.1269 16.9601 15.5 16.4998 15.5H1.49984C1.0396 15.5 0.666504 15.1269 0.666504 14.6667V1.33333C0.666504 0.8731 1.0396 0.5 1.49984 0.5ZM15.6665 4.0316L9.05967 9.94833L2.33317 4.01328V13.8333H15.6665V4.0316ZM2.75939 2.16667L9.05142 7.71833L15.2507 2.16667H2.75939Z"
                            fill="#747985" />
                    </svg>
                    <span>Inbox</span>
                    @if (Auth::user()->unreadMessagesCount())
                        <span class="number-badge message-count">{{ Auth::user()->unreadMessagesCount() }}</span>
                    @endif
                </a>
            </li>

            <li
                class="tooltip-wrap {{ request()->routeIs('campaign.send') ? 'active' : '' }} {{ $isClinicSuspended || Auth::user()->hasFailedSubscriptionPayment() || Auth::user()->hasSubscriptionPaymentPending() || !Auth::user()->activeSubscription ? 'disabled' : '' }}">
                @if ($isClinicSuspended)
                    <div class="info-tooltip">
                        <div class="tooltip-icon">
                @endif
                <a href="{{ route('campaign.send') }}">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M17.1054 1.4643L12.561 17.3695C12.4352 17.8099 12.1647 17.8299 11.9634 17.4274L8.16647 9.83352L0.602215 6.80784C0.177482 6.63795 0.18274 6.3837 0.63059 6.23442L16.5358 0.932693C16.9761 0.785893 17.2287 1.0324 17.1054 1.4643ZM14.8625 3.24724L4.67664 6.64256L9.3738 8.52143L11.9077 13.5892L14.8625 3.24724Z"
                            fill="#747985" />
                    </svg>
                    <span>Send</span>
                </a>
                @if ($isClinicSuspended)
    </div>
    <div class="tooltip-text">This feature is currently disabled due to account suspension.
        <div class="tooltip-arrow" data-arrow></div>
    </div>
</div>
@endif
</li>
<li
    class="tooltip-wrap has-submenu {{ request()->routeIs('patient') || request()->routeIs('patients.import.index') ? 'active' : '' }} {{ $isClinicSuspended || !Auth::user()->activeSubscription || Auth::user()->hasLimitedAccessDueToFailedPayment() || Auth::user()->hasLimitedAccessDueToPendingPayment() ? 'disabled' : '' }}">
    @if ($isClinicSuspended)
        <div class="info-tooltip">
            <div class="tooltip-icon">
    @endif
    <a href="javascript:void(0);">
        <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M0.666504 18.3335C0.666504 14.6516 3.65127 11.6668 7.33317 11.6668C11.0151 11.6668 13.9998 14.6516 13.9998 18.3335H12.3332C12.3332 15.5721 10.0946 13.3335 7.33317 13.3335C4.57175 13.3335 2.33317 15.5721 2.33317 18.3335H0.666504ZM7.33317 10.8335C4.57067 10.8335 2.33317 8.596 2.33317 5.8335C2.33317 3.071 4.57067 0.833496 7.33317 0.833496C10.0957 0.833496 12.3332 3.071 12.3332 5.8335C12.3332 8.596 10.0957 10.8335 7.33317 10.8335ZM7.33317 9.16683C9.17484 9.16683 10.6665 7.67516 10.6665 5.8335C10.6665 3.99183 9.17484 2.50016 7.33317 2.50016C5.4915 2.50016 3.99984 3.99183 3.99984 5.8335C3.99984 7.67516 5.4915 9.16683 7.33317 9.16683ZM14.2363 12.2525C16.5535 13.2969 18.1665 15.6268 18.1665 18.3335H16.4998C16.4998 16.3035 15.2901 14.5561 13.5522 13.7727L14.2363 12.2525ZM13.6633 2.8445C15.3285 3.53102 16.4998 5.16984 16.4998 7.0835C16.4998 9.47533 14.67 11.4378 12.3332 11.6482V9.97066C13.7471 9.76866 14.8332 8.5535 14.8332 7.0835C14.8332 5.93295 14.1678 4.93852 13.2007 4.46379L13.6633 2.8445Z"
                fill="#747985" />
        </svg>
        <span>Patients</span>
        <em class="arrow-icon-wrapper">
            <img src="{{ asset('front/images/sidebar-accordion-arrow.svg') }}" alt="right arrow" />
        </em>
    </a>
    @if ($isClinicSuspended)
        </div>
        <div class="tooltip-text">This feature is currently disabled due to account suspension.
            <div class="tooltip-arrow" data-arrow></div>
        </div>
        </div>
    @endif
    <ul class="submenu {{ request()->routeIs('patient*') ? 'active' : '' }}">
        <li
            class="{{ request()->routeIs('patient') ? 'page-active' : '' }} {{ $isClinicSuspended ? 'disabled' : '' }}">
            <a href="{{ route('patient') }}" title="Patient List">Patient List</a>
        </li>
        <li
            class="{{ request()->routeIs('patients.import.index') ? 'page-active' : '' }} {{ $isClinicSuspended ? 'disabled' : '' }}">
            <a href="{{ route('patients.import.index') }}" title="Import Patients">Import Patients</a>
        </li>
    </ul>
</li>
<li
    class="tooltip-wrap {{ request()->routeIs('template.index') ? 'active' : '' }} {{ $isClinicSuspended || !Auth::user()->activeSubscription || Auth::user()->hasLimitedAccessDueToFailedPayment() || Auth::user()->hasLimitedAccessDueToPendingPayment() ? 'disabled' : '' }}">
    @if ($isClinicSuspended)
        <div class="info-tooltip">
            <div class="tooltip-icon">
    @endif
    <a href="{{ route('template.index') }}">
        <svg width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M2.16667 5.6665V15.6665H13.8333V5.6665H2.16667ZM2.16667 3.99984H13.8333V2.33317H2.16667V3.99984ZM14.6667 17.3332H1.33333C0.8731 17.3332 0.5 16.9601 0.5 16.4998V1.49984C0.5 1.0396 0.8731 0.666504 1.33333 0.666504H14.6667C15.1269 0.666504 15.5 1.0396 15.5 1.49984V16.4998C15.5 16.9601 15.1269 17.3332 14.6667 17.3332ZM3.83333 7.33317H7.16667V10.6665H3.83333V7.33317ZM3.83333 12.3332H12.1667V13.9998H3.83333V12.3332ZM8.83333 8.1665H12.1667V9.83317H8.83333V8.1665Z"
                fill="#747985" />
        </svg>
        <span>Templates</span>
    </a>
    @if ($isClinicSuspended)
        </div>
        <div class="tooltip-text">This feature is currently disabled due to account suspension.
            <div class="tooltip-arrow" data-arrow></div>
        </div>
        </div>
    @endif
</li>
<li class="has-submenu {{ Route::is('settings.*') ? 'active' : '' }}">
    <a href="javascript:void(0);">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M7.23848 3.33324L9.41067 1.16107C9.73609 0.835632 10.2638 0.835632 10.5892 1.16107L12.7613 3.33324H15.8333C16.2935 3.33324 16.6666 3.70634 16.6666 4.16657V7.23848L18.8388 9.41067C19.1642 9.73609 19.1642 10.2638 18.8388 10.5892L16.6666 12.7613V15.8333C16.6666 16.2935 16.2935 16.6666 15.8333 16.6666H12.7613L10.5892 18.8388C10.2638 19.1642 9.73609 19.1642 9.41067 18.8388L7.23848 16.6666H4.16657C3.70634 16.6666 3.33324 16.2935 3.33324 15.8333V12.7613L1.16107 10.5892C0.835632 10.2638 0.835632 9.73609 1.16107 9.41067L3.33324 7.23848V4.16657C3.33324 3.70634 3.70634 3.33324 4.16657 3.33324H7.23848ZM4.99991 4.99991V7.92884L2.92884 9.99992L4.99991 12.071V14.9999H7.92884L9.99992 17.071L12.071 14.9999H14.9999V12.071L17.071 9.99992L14.9999 7.92884V4.99991H12.071L9.99992 2.92884L7.92884 4.99991H4.99991ZM9.99992 13.3333C8.15896 13.3333 6.66657 11.8408 6.66657 9.99992C6.66657 8.15896 8.15896 6.66657 9.99992 6.66657C11.8408 6.66657 13.3333 8.15896 13.3333 9.99992C13.3333 11.8408 11.8408 13.3333 9.99992 13.3333ZM9.99992 11.6666C10.9204 11.6666 11.6666 10.9204 11.6666 9.99992C11.6666 9.07942 10.9204 8.33326 9.99992 8.33326C9.07942 8.33326 8.33326 9.07942 8.33326 9.99992C8.33326 10.9204 9.07942 11.6666 9.99992 11.6666Z"
                fill="#747985" />
        </svg>
        <span>Settings</span>
        <em class="arrow-icon-wrapper">
            <img src="{{ asset('front/images/sidebar-accordion-arrow.svg') }}" alt="right arrow" />
        </em>
    </a>
    <ul class="submenu {{ Route::is('settings.*') || Route::is('subscription.*') ? 'active' : '' }}">
        <li
            class="{{ Route::is('settings.activity.*') ? 'page-active' : '' }} {{ !Auth::user()->activeSubscription || Auth::user()->hasLimitedAccessDueToFailedPayment() || Auth::user()->hasLimitedAccessDueToPendingPayment() ? 'disabled' : '' }}">
            <a href="{{ route('settings.activity.index') }}" title="Campaign Activity">Campaign Activity</a>
        </li>
        <li
            class="tooltip-wrap {{ Route::is('settings.insurance.*') ? 'page-active' : '' }} {{ $isClinicSuspended || !Auth::user()->activeSubscription || Auth::user()->hasLimitedAccessDueToFailedPayment() || Auth::user()->hasLimitedAccessDueToPendingPayment() ? 'disabled' : '' }}">
            @if ($isClinicSuspended)
                <div class="info-tooltip">
                    <div class="tooltip-icon">
            @endif
            <a href="{{ route('settings.insurance.index') }}"
                title="Insurance">{{ __('messages.page_texts.insurance') }}</a>
            @if ($isClinicSuspended)
                </div>
                <div class="tooltip-text">This feature is currently disabled due to account suspension.
                    <div class="tooltip-arrow" data-arrow></div>
                </div>
                </div>
            @endif
        </li>
        <li class="{{ Route::is('settings.activity-log.*') ? 'page-active' : '' }}">
            <a href="{{ route('settings.activity-log.index') }}" title="Activity Log">Activity Log</a>
        </li>
        <li class="{{ Route::is('subscription.*') ? 'page-active' : '' }}">
            <a href="{{ route('subscription.index') }}" title="Subscription">Subscription</a>
        </li>
    </ul>
</li>
</ul>
<div class="user-info">
    <a href="javascript:void(0);" class="nav-link {{ request()->routeIs('profile*') ? 'active' : '' }}">
        <span>{{ Auth::user()->company_name }}</span>
        <em>
            <img src="{{ asset('front/images/sidebar-right-arrow.svg') }}" alt="right arrow" class="normal-arrow" />
            <img src="{{ asset('front/images/sidebar-right-blue-arrow.svg') }}" alt="right arrow"
                class="active-arrow" />
        </em>
    </a>
    <div class="profile-info">
        <ul>
            <li>
                <a href="{{ route('profile') }}" title="Profile">Profile</a>
            </li>
            <li>
                <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                    @csrf
                </form>
                <a href="#" title="Log out" class="logout-link">Log out</a>
            </li>
        </ul>
    </div>
</div>
</div>
</div>
<div class="sidebar-overlay"></div>
