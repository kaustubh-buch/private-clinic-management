<script src="{{ asset('front/js/jquery.min.js') }}"></script>
<script src="{{ asset('front/js/jquery-migrate.min.js') }}"></script>
<script src="{{ asset('front/js/jquery.smartWizard.js')}}"></script>
<script src="{{asset('front/js/jquery.validate.min.js')}}"></script>
<script src="{{ asset('front/js/select2.min.js') }}"></script>
<script src="{{ asset('front/js/toastr.min.js') }}"></script>
<script src="{{ asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('front/js/jquery.mask.min.js') }}"></script>
<script src="{{ asset('front/js/floatingui-core.js')}}"></script>
<script src="{{ asset('front/js/floatingui-dom.js')}}"></script>
<script src="{{ asset('front/js/dataTables.min.js') }}"></script>
<script src="{{ asset('front/js/flatpickr.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="{{ asset('front/js/general.js') }}"></script>

<script>
    var commonWebsiteSettings = {!! json_encode(WebsiteSettings::getWebsiteSettings()) !!};
    const loginUrl = @json(route('login'));
    const clinicStoreUrl = @json(route('clinic.store'));
</script>
<script src="{{ asset('front/js/common.js') }}"></script>
<script>
    $('#markAllRead').on('click', function() {
        $.post("{{ route('notification.markAllAsRead') }}", {_token: '{{ csrf_token() }}'}, function(res) {
            if(res.success) {
                $('.notification-icon').removeClass('has-notification-dot');
                $('#notificationCount').text('0');
                $('#markAllRead').hide();
                $('.noty-wrap-item').html('No Notifications found.');
                $('.noty-wrap-item').css('height', 'auto');
                hideLoader();
            }
        });
    });
    @if (Auth::user())
    let expirationTime = "{{ \Carbon\Carbon::now()->subDay()->timestamp * 1000 }}";
    let now = new Date().getTime();
    @if(!empty(Auth::user()->clinics))
    let accountStatus = "{{ Auth::user()->clinics->is_suspended }}";
    if (accountStatus == 1) {
        let lastShown = localStorage.getItem("suspended_popup_shown");
        if (!lastShown || lastShown < expirationTime) {
            openDefaultModal('account-suspended');
            localStorage.setItem("suspended_popup_shown", now);
        }
    }
    @endif
    @if (Auth::user()->hasFailedSubscriptionPayment())
        let isFailedShown = localStorage.getItem("payment_failed_shown");
        if (!isFailedShown || isFailedShown < expirationTime) {
            openDefaultModal('payment-failed');
            localStorage.setItem("payment_failed_shown", now);
        }
    @endif
    $(".close-top-header").on("click", function () {
        const userId = "{{  Auth::user()->id }}";
        const cookieName = "hide_blue_bar_until_" + userId;
        let expiryDate = new Date();
        expiryDate.setTime(expiryDate.getTime() + (24 * 60 * 60 * 1000)); 
        document.cookie = cookieName + "=" + Math.floor(expiryDate.getTime() / 1000) +
            "; path=/; expires=" + expiryDate.toUTCString();
        $(".blue-bar-content").hide();
    });
    @endif
    @if (Session::has('success'))
        showCustomToast("{{ session('success') }}",'success',true);
    @elseif (Session::has('error'))
        showCustomToast(@json(session('error')),'error',true);
    @endif

    @if (Auth::user() && Auth::user()->isFreeTrialLastDay())
        openDefaultModal('free-trial-end-soon')
    @endif
</script>
<script>
    @if (Auth::user())
        checkExportStatus();
    @endif
</script>

