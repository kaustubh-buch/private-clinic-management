<p>{{ __('messages.page_texts.hi') }} {{ $user->first_name }},</p>
<p>{!! __('messages.page_texts.reset_password_body') !!}</p>
<p><a href="{{ $resetUrl }}" title="{{ __('messages.button.reset_password') }}"
style="background-color: #465DFF;padding: 10px;border: none;border-radius: 4px;color: white;font-size: 14px;font-weight: 500" target="_blank">
{{ __('messages.button.reset_password') }}</a></p>
<p>{{ __('messages.page_texts.reset_link_expiry_notice') }}</p>
<p>{{ __('messages.page_texts.email_greetings') }}</p>
<p>{{ __('messages.page_texts.team_signature') }}</p>