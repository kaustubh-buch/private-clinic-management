$(document).ready(function () {
    hideLoader();

    $.validator.setDefaults({
        errorElement: "div",
        errorClass: "invalid-feedback",
        highlight: function (element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function (element) {
            $(element).removeClass("is-invalid");
        },
        errorPlacement: function (error, element) {
            const group = element.closest(".input-group");
            const append = group.find(".input-group-append");
            if (append.length > 0) {
                append.after(error);
            }else {
                element.after(error);
            }
        },
    });

    $.extend(jQuery.validator.messages, {
        required: function(status, elem) {
            var name = $(elem).data('validator-name');
            return name ? name + " is required." : "This field is required.";
        },
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
            showLoader();
        },
        complete: function () {
            hideLoader();
        }
    });
    $("body").tooltip({selector:'[data-toggle=tooltip]', boundary: 'window'});

    $('.image-open').on('click', function () {
        var imageUrl = $(this).attr('src');
        window.open(imageUrl, '_blank');
    });
});

$.validator.addMethod(
    "dateRange",
    (value, element) => {
        if (value === "") return true; // allow empty input
        const singleDatePattern = getPatternFromString(commonWebsiteSettings.patterns.SINGLE_DATE_PATTERN);
        const rangeDatePattern = getPatternFromString(commonWebsiteSettings.patterns.RANGE_DATE_PATTERN);

        return singleDatePattern.test(value) || rangeDatePattern.test(value);
    },
    commonWebsiteSettings.messages.date_range_validation_message
);


$.validator.addMethod(
    "passwordComplexity",
    (value, element) =>
        getPatternFromString(commonWebsiteSettings.patterns.PASSWORD).test(
            value
        ),
    commonWebsiteSettings.messages.password_complexity
);


$.validator.addMethod(
    "NameValidation",
    (value, element) =>
        getPatternFromString(commonWebsiteSettings.patterns.NAME).test(
            value
        ),
    commonWebsiteSettings.messages.name_validation_message
);

$.validator.addMethod(
    "EmailValidation",
    (value, element) =>
        getPatternFromString(commonWebsiteSettings.patterns.EMAIL).test(
            value
        ),
    commonWebsiteSettings.messages.email_validation_message
);

$.validator.addMethod(
    "PhoneValidation",
    (value, element) =>
        getPatternFromString(commonWebsiteSettings.patterns.PHONE).test(
            value
        ),
    commonWebsiteSettings.messages.phone_validation_message
);

$.validator.addMethod(
    "MobileValidation",
    (value, element) =>
        getPatternFromString(commonWebsiteSettings.patterns.MOBILE_NO).test(
            value
        ),
    commonWebsiteSettings.messages.mobile_validation_message
);

$.validator.addMethod(
    "ContactValidation",
    (value, element) =>
        getPatternFromString(commonWebsiteSettings.patterns.CONTACT_NO).test(
            value
        ),
    commonWebsiteSettings.messages.contact_validation_message
);


function getPatternFromString(patternString) {
    // Remove leading and trailing slashes from the pattern string
    var cleanedPatternString = patternString.replace(/^\/|\/$/g, "");

    // Create and return a RegExp object from the cleaned pattern string
    return new RegExp(cleanedPatternString);
}

function handleAjaxError(error, formName = '') {
    if (error.responseJSON && error.responseJSON.errors && error.status === 422) {
        var validator = $("#" + formName).validate();
        $.each(error.responseJSON.errors, function (field, message) {
            var errorObject = {};
            errorObject[field] = message[0];
            validator.showErrors(errorObject);
        });
    } else if (error.status === 403) {
        var message = error.responseJSON.message != '' ? error.responseJSON.message : commonWebsiteSettings.messages.unauthorized_action;
        showToastMessage(message, 'error');
        if (error.responseJSON.route)
        {
            setTimeout(function() {
                window.location.href = error.responseJSON.route;
            }, 2000);
        }
    } else if (error.status === 419) {
        showToastMessage(commonWebsiteSettings.messages.session_expired, 'error');
        setTimeout(function() {
            window.location.reload();
        }, 2000);
    }
    else {
        showToastMessage(commonWebsiteSettings.messages.unexpected_error, 'error');
    }
}

$.fn.dataTable.ext.errMode = function ( settings, helpPage, message ) {
    handleAjaxError(settings.jqXHR);
};

function locationReload(){
    window.location.reload();
}

function hideModal(modalId){
    $(`#${modalId}`).modal('hide');
}

function showModal(modalId){
    $(`#${modalId}`).modal('show');
}

function showToastMessage(message,type) {
    if (typeof toastr === 'undefined') {
        console.error('Toastr not loaded');
        return;
    }

    switch (type) {
        case 'success':
            toastr.success(message);
            break;
        case 'error':
            toastr.error(message);
            break;
        case 'info':
            toastr.info(message);
            break;
        case 'warning':
            toastr.warning(message);
            break;
        default:
            console.warn("Unknown toastr type:", type);
    }
}

function ajaxPost(url, data, onSuccess, onError) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: onSuccess,
        error: onError,
    });
}

function showLoader() {
    $("#pageLoader").show();
}

function hideLoader() {
    $("#pageLoader").hide();
}

function isPageLoading() {
    return $("#pageLoader").is(":visible");
}
