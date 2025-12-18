$(document).ready(function () {
    const $inputs = $('.otp-input');
    const $otpHidden = $('#otp-value');

    function updateOtpValue() {
        let otp = '';
        $inputs.each(function () {
            otp += $(this).val();
        });
        $otpHidden.val(otp);
        if($otpHidden.hasClass('is-invalid')) {
            $otpHidden.valid();
        }
    }

    // Auto-move and collect digits
    $inputs.on('input', function () {
        const index = $inputs.index(this);
        const value = $(this).val().slice(0, 1); // limit to 1 character
        $(this).val(value);

        if (value && index < $inputs.length - 1) {
            $inputs.eq(index + 1).focus();
        }

        if($('#secure-account-stepper').length > 0) {
            fixWizardHeightDebounced($('#secure-account-stepper'));
        }

        updateOtpValue();
    });

    // Backspace move left
    $inputs.on('keydown', function (e) {
        const index = $inputs.index(this);
        if (e.key === 'Backspace' && !$(this).val() && index > 0) {
            $inputs.eq(index - 1).focus();
        }
    });

    // Paste entire OTP
    $inputs.on('paste', function (e) {
        e.preventDefault();
        const pasteData = e.originalEvent.clipboardData.getData('text').replace(/\D/g, '').substring(0, 6);

        for (let i = 0; i < pasteData.length; i++) {
            $inputs.eq(i).val(pasteData[i]);
        }

        updateOtpValue();
        $inputs.eq(pasteData.length - 1).focus();
    });


    const otpValidations = {
                required: true,
                digits: true,
                minlength: commonWebsiteSettings.max_length.OTP,
                maxlength: commonWebsiteSettings.max_length.OTP
            };

    const otpMessages =  {
                required: commonWebsiteSettings.messages.otp_required,
                digits: commonWebsiteSettings.messages.otp_digit_only,
                minlength: commonWebsiteSettings.messages.otp_length,
                maxlength: commonWebsiteSettings.messages.otp_length
            };
    // jQuery Validation
    $('#otpForm').validate({
        ignore:[],
        rules: {
            otp: otpValidations,

        },
        messages: {
            otp: otpMessages,

        },

        errorPlacement: function(error, element) {
            if($(element).attr('name') == 'otp'){
                error.insertAfter(element.parent());
                // $('.otp-error-message').html(error.text());
            }
        },
        success: function(label,element) {
            if($(element).attr('name') == 'otp'){
                $('.otp-error-message').html('');
            }
        },
        submitHandler: function (form) {
            showLoader();
            updateOtpValue();
            form.submit();
        }
    });

    $('.resend-link').on('click',function(){
        showLoader();
    });

    function handleResponse(context){

        const $sendCodeBtn = $('.send-code-btn');

        if(context == 'password_reset'){
            $sendCodeBtn.attr('type','submit');
            $sendCodeBtn.text('Continue');
            $sendCodeBtn.removeClass('send-code-btn').addClass('verify-btn');
            $('.resend-sms-text').removeClass('d-none');
            $('.otp-input').attr('disabled',false);
        }
        showCustomToast(commonWebsiteSettings.messages.otp_sent_success,'success', true);

    }

    $('.send-code-btn').on('click',function(){
        const context = $(this).data('context');
        if(context == 'password_reset' && $(this).hasClass('verify-btn')){
            return;
        }

        $.ajax({
            url:  commonWebsiteSettings.routes.send_otp,
            type: 'POST',
            success(response) {
               handleResponse(context);
            },
            error(xhr) {
                handleAjaxError(xhr, "Failed to send OTP. Please try again.");
            }
        });
    });

    function makePayload(context){

        switch (context) {
            case 'verify_mobile':
                return { mobile_no: $('#mobile-number').val() };
            case 'mobile_change':
                return { mobile_no: $('#new-phn-number').val() };
            default:
                return null;
        }
    }

    $('#resend-ajax-link').on('click',function(e){
        e.preventDefault();

        const context = $(this).data('context');

        let data = makePayload(context);
        $.ajax({
            url: commonWebsiteSettings.routes.resend_otp,
            type: 'GET',
            data:data,
            success(response) {
               if(response.success){
                    showCustomToast(response.message,'success',true);
               }else{
                    if(response.secondsLeft){
                        countdownTimer(true,response.secondsLeft);
                    }
               }

            }
        })
    })
});
