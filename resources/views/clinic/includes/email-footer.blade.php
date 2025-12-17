<tr>
    <td height="24"></td>
</tr>
@if(empty($hideFooter))
<tr>
    <td style="color: #21252980;font-size: 14px;font-weight: 400;line-height:1.4;">
        {{ __('messages.page_texts.need_support') }} <a href="{{config('constants.CONTACT_SUPPORT')}}" title="Contact Us" target="_blank"
            style="color: #212529;font-size: 14px;font-weight: 500;line-height:1.4;text-decoration: underline;">{{ __('messages.page_texts.contact_us') }}</a></td>
</tr>
@endif
<tr>
    <td height="16"></td>
</tr>
