@extends('clinic.layout.front')
@section('page-title', 'Notifications')
@section('content')
    <div class="page-wrapper">
        <div class="page-inner-wrapper">
            <div class="white-card notification-page-wrapper">
                <div class="notification-outer-wrapper">
                    <div class="notification-main-wrapper">
                        @if ($notifications->isNotEmpty())
                            <ul>
                                @foreach ($notifications as $notification )
                                    <li class="notification-item">
                                        <div class="icon-content-block">
                                            <div class="icon-wrapper green-bg">
                                                <img src="{{ asset('front/images/'. $notification->image) }}" alt="Tick Icon">
                                            </div>
                                            <div class="content-wrapper">
                                                <p><strong>{{ $notification->name }}</strong> {{ $notification->message }}</p>
                                                <span class="time">{{ $notification->formattedTime }} </span>
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('notification.destroy',['notification'=> $notification->id ])}}" class="delete-notification-form">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="delete-notification-confirmation-button" title="Delete" style="background: none; border: none; padding: 0;">
                                                <img src="{{ asset('front/images/delete-icon-red.svg') }}" alt="Delete Icon">
                                            </button>
                                        </form>
                                    </li>
                                @endforeach

                            </ul>
                        @else
                            <p class="text-center">No Notifications found.</p>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let notificationId;
            $('.delete-notification-confirmation-button').on('click', function(e) {
                showLoader();
            });
        })
    </script>
@endpush
