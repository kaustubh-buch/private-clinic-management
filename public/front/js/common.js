// Always hide loader when page is shown (normal load or back/forward from cache)
$(window).on("pageshow", function () {
    hideLoader();
});
$(document).ready(function () {
    hideLoader();
    $(".logout-link").on("click", function (e) {
        e.preventDefault();
        $("#logout-form").submit();
    });

    $(".logout-account-review-link").on("click", function () {
        showLoader();
        $(".logout-link").click();
    });

    $.validator.setDefaults({
        errorElement: "p",
        errorClass: "error-message",
        highlight: function (element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function (element) {
            $(element).removeClass("is-invalid");
        },
        errorPlacement: function (error, element) {
            if ($(element).attr("name") == "otp") {
                error.insertAfter(element.parent());
            } else if ($(element).hasClass("mobile-number")) {
                const wrapper = element.closest(".input-btn-outer-wrapper");
                wrapper.append(error);
            } else if ($(element).attr("name") == "message") {
                error.insertAfter(".message-wrapper");
            } else if ($(element).hasClass("select2-hidden-accessible")) {
                error.insertAfter(element.next(".select2"));
            } else if ($(element).attr("name") == "agreed") {
                error.insertAfter(element.parent());
            } else if($(element).attr('type') == 'radio') {
                error.insertAfter(element.parents('.radio-button-wrapper'));
            } else {
                error.insertAfter(element);
            }
        },
    });

    $("#go-to-login-btn").on("click", function () {
        window.location.href = loginUrl;
    });

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: function (xhr, settings) {
            if (settings.showLoader !== false) {
                showLoader();
            }
            if(settings.showIndexLoader === true){
                indexShowLoader();
            }
        },
        complete: function () {
            hideLoader();
            indexHideLoader();
        }
    });

    $(document).ajaxError(function (event, jqxhr, settings, thrownError) {
        handleAjaxError(jqxhr);
    });
 
    
    $.fn.dataTable.ext.errMode = function ( settings, helpPage, message ) {
        handleAjaxError(settings.jqXHR);
    };

    $(document).on("keydown", ".input-number", function (event) {
        const allowedKeys = [8, 9, 13, 16, 37, 39]; // backspace, tab, enter, shift, arrows
        const isNumberKey =
            (event.which >= 48 && event.which <= 57) || // top row
            (event.which >= 96 && event.which <= 105); // numpad

        if (!allowedKeys.includes(event.which) && !isNumberKey) {
            event.preventDefault();
            return;
        }

        if (isNumberKey) {
            const input = this;
            const value = $(input).val();
            const selectionStart = input.selectionStart;
            const selectionEnd = input.selectionEnd;
            const selectedLength = selectionEnd - selectionStart;

            const finalLength = value.length - selectedLength + 1;

            const maxLength = $(input).attr("maxlength") || 10;
            if (finalLength > maxLength) {
                event.preventDefault();
            }
        }
    });

    $("input").attr("autocomplete", "off");
});

function handleAjaxError(error, customMessage = '') {
    if (error.status === 401 || error.status === 403) {
        var message = error.responseJSON.message != '' ? error.responseJSON.message : 'Unauthorized action.';
        showCustomToast(message, 'error');
        if (error.responseJSON.route)
        {
            setTimeout(function() {
                window.location.href = error.responseJSON.route;
            }, 2000);
        }
    } else if (error.status === 419) {
        showCustomToast('The page is expired due to inactivity.', 'error');
        setTimeout(function() {
            window.location.reload();
        }, 2000);
    }else if (error.status === 422) {
        return;
    }else {
        const msg = customMessage || commonWebsiteSettings.messages.something_went_wrong;
        showCustomToast(msg, 'error', true);
    }
}


function getPatternFromString(patternString) {
    // Remove leading and trailing slashes from the pattern string
    var cleanedPatternString = patternString.replace(/^\/|\/$/g, "");

    // Create and return a RegExp object from the cleaned pattern string
    return new RegExp(cleanedPatternString);
}

function validatePasswordStrength(value) {
    const hasMinLength = getPatternFromString(
        commonWebsiteSettings.patterns.PASSWORD_RULES.MIN_LENGTH
    ).test(value);
    const hasUpperCase = getPatternFromString(
        commonWebsiteSettings.patterns.PASSWORD_RULES.UPPERCASE
    ).test(value);
    const hasNumber = getPatternFromString(
        commonWebsiteSettings.patterns.PASSWORD_RULES.DIGIT
    ).test(value);
    const hasSpecialChar = getPatternFromString(
        commonWebsiteSettings.patterns.PASSWORD_RULES.SPECIAL_CHAR
    ).test(value);

    $("#password-min-length")
        .toggleClass("right", hasMinLength)
        .toggleClass("wrong", !hasMinLength);
    $("#password-uppercase")
        .toggleClass("right", hasUpperCase)
        .toggleClass("wrong", !hasUpperCase);
    $("#password-number")
        .toggleClass("right", hasNumber)
        .toggleClass("wrong", !hasNumber);
    $("#password-special-char")
        .toggleClass("right", hasSpecialChar)
        .toggleClass("wrong", !hasSpecialChar);
    return hasMinLength && hasUpperCase && hasNumber && hasSpecialChar;
}

function showLoader() {
    $("#pageLoader").show();
}

function hideLoader() {
    $("#pageLoader").hide();
}

function indexShowLoader() {
    $("#indexPageLoader").css("display", "flex");
}

function indexHideLoader() {
    $("#indexPageLoader").hide();
}

function isPageLoading() {
    return $("#pageLoader").is(":visible");
}

$.validator.addMethod(
    "strongPassword",
    function (value) {
        return validatePasswordStrength(value);
    },
    commonWebsiteSettings.messages.strong_password
);

$.validator.addMethod("notEqualToCurrentPassword", function(value, element) {
    let currentPassword = $('[name="current_password"]').val();
    return value !== currentPassword;
}, commonWebsiteSettings.messages.not_same_as_current_password);


$.validator.addMethod(
    "notInFuture",
    function (value, element) {
        var parts = value.split("-");
        if (parts.length !== 3) {
            return false;
        }
        var day = parseInt(parts[0]);
        var month = parseInt(parts[1]);
        var year = parseInt(parts[2]);

        var inputDate = new Date(year, month - 1, day);
        var currentDate = new Date();

        return inputDate <= currentDate;
    },
    commonWebsiteSettings.messages.dob_not_in_future
);

$.validator.addMethod(
    "dateITA",
    function (value, element) {
        var check = false,
            re = /^\d{1,2}-\d{1,2}-\d{4}$/, // Updated regex for DD-MM-YYYY
            adata,
            gg,
            mm,
            aaaa,
            xdata;
        if (re.test(value)) {
            adata = value.split("-"); // Updated delimiter
            gg = parseInt(adata[0], 10);
            mm = parseInt(adata[1], 10);
            aaaa = parseInt(adata[2], 10);
            xdata = new Date(Date.UTC(aaaa, mm - 1, gg, 12, 0, 0, 0));
            if (
                xdata.getUTCFullYear() === aaaa &&
                xdata.getUTCMonth() === mm - 1 &&
                xdata.getUTCDate() === gg
            ) {
                check = true;
            }
        }
        return this.optional(element) || check;
    },
    $.validator.messages.date
);

function openDefaultModal(_this) {
    $("body,html").addClass("modal-open");
    var _currentModal = $(".custom-modal[data-target='" + _this + "']");
    _currentModal.addClass("visible");

    setTimeout(function () {
        _currentModal.addClass("fadein");
    }, 125);
}

function previewMessage(text, length = 50) {
    if (!text) return "";

    // Trim to desired length
    text = text.length > length
        ? text.substring(0, length) + "..."
        : text;
    
    return textToHtml(text, true);
}

function textToHtml(text, noBr = false) {
  if (!text) return "";

  // Escape HTML entities
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;"
  };

  const escaped = text.replace(/[&<>"']/g, m => map[m]);

  // Replace newlines with <br>
  return noBr ? escaped : escaped.replace(/\r?\n/g, "<br>");
}

function displayServerSideError(errors) {
    $.each(errors, function (field, messages) {
        var inputField = $('[name="' + field + '"]');
        const formGroup = inputField.closest(".form-group");

        if (
            messages[0].includes(
                commonWebsiteSettings.messages.too_many_attempts
            )
        ) {
            $(".logout-link").click();
        } else {
            formGroup.find(".error-message").show();
            formGroup.find(".error-message").html(messages[0]);
        }
    });
}

$.validator.addMethod("regex", function (value, element, pattern) {
    if (this.optional(element)) {
        return true;
    }
    var re = new RegExp(pattern);
    return re.test(value);
});

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function dateInputMask(elm) {
    $(elm).mask("00-00-0000");
}

function restrictDateInputSelection(elm) {
    let length = elm.value.length;
    elm.setSelectionRange(length, length);
}

$.validator.addMethod(
    "NameValidation",
    (value, element) =>
        getPatternFromString(commonWebsiteSettings.patterns.NAME).test(value),
    commonWebsiteSettings.messages.name_validation_message
);
const fixWizardHeightDebounced = debounce(($stepper) => {
    if ($stepper && $stepper.smartWizard) {
        $stepper.smartWizard("fixHeight");
    }
}, 300);

function initializeStripeElements(
    stripe,
    cardNumberId,
    cardExpiryId,
    cardCvcId
) {
    var elements = stripe.elements({});

    var elementStyles = {
        base: {
            // fontSize: '18px',
            fontSmoothing: "antialiased",
            color: "#333",

            lineHeight: "44px",
            padding: "0 14px",

            fontSize: "13px",
            height: "44px",
            border: "1px solid lightgray",
            padding: "0 14px",
            borderRadius: "4px",
            /* line-height: 2rem; */
            outline: "0",
        },
        invalid: {
            color: "#333",
        },
    };

    var cardNumber = elements.create("cardNumber", {
        style: elementStyles,
        placeholder: "0000-0000-0000-0000",
    });
    cardNumber.mount("#" + cardNumberId);

    var cardExpiry = elements.create("cardExpiry", {
        style: elementStyles,
    });
    cardExpiry.mount("#" + cardExpiryId);

    var cardCvc = elements.create("cardCvc", {
        style: elementStyles,
        placeholder: "000",
    });
    cardCvc.mount("#" + cardCvcId);

    cardNumber.on("ready", function () {
        elementsReady();
    });
    cardExpiry.on("ready", function () {
        elementsReady();
    });
    cardCvc.on("ready", function () {
        elementsReady();
    });

    cardNumber.on("change", function (event) {
        if (!event.error) {
            $("#cardNumberError").text("");
        }
    });

    cardExpiry.on("change", function (event) {
        if (!event.error) {
            $("#cardExpiryError").text("");
        }
    });

    cardCvc.on("change", function (event) {
        if (!event.error) {
            $("#cardCVVError").text("");
        }
    });

    var cardElements = {
        cardNumber: cardNumber,
        cardExpiry: cardExpiry,
        cardCvc: cardCvc,
    };

    return cardElements;
}

function handlePaymentSubmission(
    formId,
    stripeElement,
    cardElements,
    callback,
    paymentMethodMessages = {}
) {
    $("#" + formId).validate({
        rules: {
            name: {
                required: true,
            },
        },
        messages: paymentMethodMessages,
        highlight: function (element) {
            fixWizardHeightDebounced($("#secure-account-stepper"));
        },
        unhighlight: function (element) {
            fixWizardHeightDebounced($("#secure-account-stepper"));
        },
        submitHandler: function () {
            showLoader();

            var cardName = $("#card-holder-name").val();

            stripeElement
                .createPaymentMethod({
                    type: "card",
                    card: cardElements["cardNumber"],
                    billing_details: {
                        name: cardName,
                    },
                })
                .then(function (result) {
                    if (result.error) {
                        $("#cardNumberError").text("");
                        $("#cardExpiryError").text("");
                        $("#cardCVVError").text("");
                        switch (result.error.code) {
                            case "invalid_number":
                            case "incomplete_number":
                                $("#cardNumberError").text(
                                    result.error.message
                                );
                                break;
                            case "invalid_expiry_month":
                            case "incomplete_expiry":
                            case "invalid_expiry_year":
                            case "invalid_expiry_year_past":
                                $("#cardExpiryError").text(
                                    result.error.message
                                );
                                break;
                            case "invalid_cvc":
                            case "incomplete_cvc":
                                $("#cardCVVError").text(result.error.message);
                                break;
                            default:
                            // showToastMessage(result.error.message, 'error');
                        }
                        hideLoader();
                    } else {
                        callback(result);
                    }
                });
        },
    });
}

// Accept a value from a file input based on a required mimetype
$.validator.addMethod("accept", function (value, element, param) {
    // Split mime on commas in case we have multiple types we can accept
    var typeParam =
            typeof param === "string" ? param.replace(/\s/g, "") : "image/*",
        optionalValue = this.optional(element),
        i,
        file,
        regex;

    // Element is optional
    if (optionalValue) {
        return optionalValue;
    }

    if ($(element).attr("type") === "file") {
        // Escape string to be used in the regex
        // see: https://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
        // Escape also "/*" as "/.*" as a wildcard
        typeParam = typeParam
            .replace(/[\-\[\]\/\{\}\(\)\+\?\.\\\^\$\|]/g, "\\$&")
            .replace(/,/g, "|")
            .replace(/\/\*/g, "/.*");

        // Check if the element has a FileList before checking each file
        if (element.files && element.files.length) {
            regex = new RegExp(".?(" + typeParam + ")$", "i");
            for (i = 0; i < element.files.length; i++) {
                file = element.files[i];

                // Grab the mimetype from the loaded file, verify it matches
                if (!file.type.match(regex)) {
                    return false;
                }
            }
        }
    }

    // Either return true because we've validated each file, or because the
    // browser does not support element.files and the FileList feature
    return true;
});

function closeModal(_currentModal) {
    var _currentModal = jQuery(
        ".custom-modal[data-target='" + _currentModal + "']"
    );
    _currentModal.removeClass("fadein");
    setTimeout(function () {
        _currentModal.removeClass("visible");
    }, 125);
    $('html,body').removeClass('modal-open');
}

//reset modal
function resetModalForm(modalSelector) {
    const $modal = $(modalSelector);
    const $form = $modal.find('form');
  
    if ($form.length) {
        $form[0].reset(); // Reset form fields
        $form.find('select').each(function() {
            $(this).val('');        
            $(this).trigger('change');
        });    
        $form.validate?.().resetForm(); // Clear validation errors (if using jQuery Validate)
        $form.find('.form-control').removeClass('is-invalid'); // Remove Bootstrap classes if used    
    }
}

$.validator.addMethod("extension", function (value, element, param) {
    param =
        typeof param === "string" ? param.replace(/,/g, "|") : "png|jpe?g|gif";
    return (
        this.optional(element) ||
        value.match(new RegExp("\\.(" + param + ")$", "i"))
    );
});

$.validator.addMethod(
    "contenteditableRequired",
    function (value, element) {
        const text = $(element).text().trimStart();
        return text.length > 0;
    },
    "Message is required."
);

$.validator.addMethod(
    "contenteditableMin",
    function (value, element, param) {
        const text = $(element).text().trimStart();
        return text.length >= param;
    },
    $.validator.format("Message must be at least {0} characters.")
);

function reInitiallizetable(tableId) {
    const $table = jQuery(`#` + tableId);

    if (!$.fn.DataTable.isDataTable($table[0])) {
        const noteText = $table.data("note");

        $table.on("init.dt", function () {
            // Append note if applicable
            const noteText = $table.data("note");
            if (noteText) {
                const $wrapper = $table.closest(".dt-layout-table");
                if (!$wrapper.next(".indicates-note").length) {
                    $wrapper.after(
                        `<div class="indicates-note">${noteText}</div>`
                    );
                }
            }
        });

        // Initialize DataTable
        $table.DataTable({
            paging: true,
            pageLength: 10,
            lengthChange: false,
            searching: false,
            info: true,
            ordering: false,
            responsive: true,
            language: {
                info: "Showing 10 of _TOTAL_ Results",
                paginate: {
                    previous: '<img src="/front/images/prev-arrow.svg">',
                    next: '<img src="/front/images/next-arrow.svg">',
                },
            },
            layout: {
                bottomEnd: {
                    paging: {
                        firstLast: false,
                    },
                },
            },
            drawCallback: function (settings) {
                var api = this.api();
                var info = api.page.info();

                var shown = info.end - info.start;
                var total = info.recordsTotal;

                $(api.table().container())
                    .find(".dt-info")
                    .html(`Showing ${shown} of ${total} Results`);
            },
        });
    }
}

function showCustomToast(text, type = "success", isTop = false) {
    $(".toast-message").html(text);
    jQuery(".toast-message-wrapper")
        .addClass("show-toast")
        .removeClass("success error")
        .addClass(type)
        .toggleClass("top-pos", isTop);
    setTimeout(function () {
        jQuery(".toast-message-wrapper").removeClass("show-toast");
    }, 6000);
}

function scrollToTop() {
    $("html, body").animate({ scrollTop: 0 }, "smooth");
}

$.extend(true, $.fn.dataTable.defaults, {
    drawCallback: function (settings) {
        var api = this.api();
        var info = api.page.info();

        var shown = info.end - info.start;
        var total = info.recordsTotal;

        // Replace default info
        $(api.table().container())
            .find(".dataTables_info, .dt-info")
            .html(`Showing ${shown} of ${total} Results`);
    },
});

function checkExportStatus() {
    $.ajax({
        type: "GET",
        url: commonWebsiteSettings.routes.export_status,
        showLoader: false,
        success: function (response) {
            if (response.success) {
                if (response.download_inprogress) {
                    if ($(".activity-log-export-btn").is(":visible")) {
                        $(".activity-log-export-btn").addClass(
                            "activity-log-export-disabled"
                        );
                    }
                    $(".activity-log-export-btn").text(
                        commonWebsiteSettings.page_texts.exporting
                    );
                    setTimeout(checkExportStatus, 3000);
                } else {
                    if (response.is_completed) {
                        if(response.download_link) {
                            window.open(response.download_link, '_self');
                        }
                        showCustomToast(
                            commonWebsiteSettings.messages
                                .file_download_success,
                            "success",
                            true
                        );
                    }
                    $(".activity-log-export-btn").text(
                        commonWebsiteSettings.page_texts.export
                    );
                    $(".activity-log-export-btn").removeClass(
                        "activity-log-export-disabled"
                    );
                }
            }
        },
        error: function (xhr) {
            handleAjaxError(xhr);
        }
    });
}

function decodeHtmlEntities(str) {
    const txt = document.createElement("textarea");
    txt.innerHTML = str;
    return txt.value;
}

function handleBackendValidationErrors(formSelector, errors) {
    const validator = $(formSelector).validate();
    $.each(errors, function (field, messages) {
        validator.showErrors({
            [field]: messages[0],
        });
    });
}

// Force reload when coming back via browser back/forward
window.addEventListener("pageshow", function (event) {
    if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
        // Page is loaded from cache or history — force a reload
        window.location.reload(true);
    }
});