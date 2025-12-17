<?php

namespace App\Services\Admin;

use Illuminate\Http\Response;

class WebsiteSettingsService
{
    public static $SETTINGS = [];

    /**
     * Function used for getting patterns and routes as well as common settings.
     *
     * @return array
     */
    public static function getWebsiteSettings()
    {
        $routes = [
            'login' => route('login'),
            'send_otp' => route('2fa.send.code'),
            'verify_otp' => route('2fa.verify.otp'),
            'resend_otp' => route('2fa.resend'),
            'export_status' => route('export.status'),
        ];

        $httpCodes = [
            'FORBIDDEN' => Response::HTTP_FORBIDDEN,
            'NOT_FOUND' => Response::HTTP_NOT_FOUND,
        ];

        self::$SETTINGS['routes'] = $routes;
        self::$SETTINGS['patterns'] = config('constants.GLOBAL.PATTERNS');
        self::$SETTINGS['max_length'] = config('constants.MAX_LENGTH');
        self::$SETTINGS['messages'] = [
            'password_complexity' => __('messages.validation.password_complexity'),
            'strong_password' => __('messages.validation.strong_password'),
            'dob_not_in_future' => __('messages.validation.dob_not_in_future'),
            'name_validation_message' => __('messages.validation.name_validation'),
            'email_validation_message' => __('messages.validation.email_validation'),
            'phone_validation_message' => __('messages.validation.phone_validation'),
            'mobile_validation_message' => __('messages.validation.mobile_no_invalid'),
            'date_range_validation_message' => __('messages.validation.date_range_validation'),
            'unauthorized_action'  => __('messages.alerts.unauthorized_action'),
            'session_expired'      => __('messages.alerts.session_expired'),
            'unexpected_error'     => __('messages.alerts.unexpected_error'),
            'contact_validation_message' => __('messages.validation.contact_no_invalid'),
            'request_another_code_message' => __('messages.page_texts.request_another_code'),
            'otp_required' => __('messages.validation.otp_required'),
            'otp_digit_only' => __('messages.validation.otp_digits_only'),
            'otp_length' => __('messages.validation.otp_length'),
            'file_download_success' => __('messages.alerts.file_download_success'),
            'otp_sent_success' => __('messages.alerts.verification_code_sent_success'),
            'too_many_attempts' => __('messages.alerts.too_many_attempts'),
            'something_went_wrong' => __('messages.global.something_went_wrong'),
        ];
        self::$SETTINGS['page_texts'] = [
            'export' => __('messages.button.export'),
            'exporting' => __('messages.button.exporting'),
        ];
        self::$SETTINGS['http_codes'] = $httpCodes;

        return self::$SETTINGS;
    }
}
