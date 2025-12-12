function fixedHeader() {
    const header = $(".header");
    if (header && window.scrollY > 0) {
        header.addClass("fixed-header");
    } else {
        header.removeClass("fixed-header");
    }
}

const inputs = $(".code-inputs input");
const otpHidden = $('#otp-value');

function updateOtpValue() {
    let otp = '';
    inputs.each(function () {
        otp += $(this).val();
    });
    otpHidden.val(otp);
    if(otpHidden.hasClass('is-invalid')) {
        otpHidden.valid();
    }
}

$(document).ready(function () {
    fixedHeader();
    const header = $(".header");
    const footer = $(".footer");
    const wrapper = $(".wrapper");
    const mainWrap = $(".main-wrap");

    if (header && footer && wrapper && mainWrap) {
        let footerHeight = footer.innerHeight();
        footer.css("margin-top", -footerHeight);
        wrapper.css("padding-bottom", footerHeight);
        let headerHeight = header.innerHeight();
        mainWrap.css("padding-top", headerHeight);
    }

    // for auth pages
    inputs.on("input", function() {
        let value = $(this).val().replace(/[^0-9]/g, ""); // only numbers
        $(this).val(value);

        if (value.length && $(this).next().length) {
            $(this).next().focus(); // move to next
        }
        updateOtpValue();
    });

    inputs.on("keydown", function(e) {
        if (e.key === "Backspace" && $(this).val() === "") {
            $(this).prev().focus(); // go back
        }
    });

    // Paste entire OTP
    inputs.on('paste', function (e) {
        e.preventDefault();
        const pasteData = e.originalEvent.clipboardData.getData('text').replace(/\D/g, '').substring(0, 6);

        for (let i = 0; i < pasteData.length; i++) {
            inputs.eq(i).val(pasteData[i]);
        }

        updateOtpValue();
        inputs.eq(pasteData.length - 1).focus();
    });

    $("#newPassword").on("keyup", function() {
        let pass = $(this).val();

        // Conditions
        let len8 = pass.length >= 8;
        let len12 = pass.length >= 12;
        let upper = /[A-Z]/.test(pass);
        let lower = /[a-z]/.test(pass);
        let num = /[0-9]/.test(pass);
        let special = /[!@#$%^&*\.]/.test(pass);

        // Update requirement icons
        $(".len").toggleClass("check", len8);
        $(".upper").toggleClass("check", upper);
        $(".num").toggleClass("check", num);
        $(".special").toggleClass("check", special);

        // Strength Level
        let score = len8 + upper + num + special;

        // Reset bars
        $(".bar").removeClass("active-weak active-medium active-strong active-very-strong");

        if (len12 && upper && lower && num && special) {
            $("#strength-text").text("Password is VERY STRONG!").removeClass().addClass("very-strong");
            $(".bar").addClass("active-very-strong");
        } else if (score === 5 || score === 4) {
            $("#strength-text").text("Password is STRONG!").removeClass().addClass("strong");
            $(".bar1, .bar2, .bar3, .bar4").addClass("active-strong");
        } else if (score === 2 || score === 3) {
            $("#strength-text").text("Password is ACCEPTABLE!").removeClass().addClass("medium");
            $(".bar1, .bar2, .bar3").addClass("active-medium");
        } else {
            $("#strength-text").text("Password is WEAK!").removeClass().addClass("weak");
            $(".bar1, .bar2").addClass("active-weak");
        }
    });
    

    $(".auth-wrapper .forgot-pwd-content").show();
    /*$(".auth-wrapper .forgot-pwd-link").click(function (e) {
        e.preventDefault();
        $(".auth-wrapper .login-content").hide();
        $(".auth-wrapper .forgot-pwd-content").show();
    });
    $(".auth-wrapper .back-to-login-link").click(function (e) {
        e.preventDefault();
        $(".auth-wrapper .forgot-pwd-content").hide();
        $(".auth-wrapper .login-content").show();
    });*/

    $(".input-password-wrap .pwd-icon-wrap").on("click", function () {
        var wrapper = $(this).closest(".input-password-wrap");
        var input = wrapper.find("input");

        if (input.attr("type") === "password") {
            input.attr("type", "text");
            wrapper.addClass("show-password");
        } else {
            input.attr("type", "password");
            wrapper.removeClass("show-password");
        }
    });

    // profile dropdown
    const profileBtn = $(".header .profile-btn");
    const profileBlock = $(".header .profile-block");

    profileBtn.click(function () {
        profileBlock.toggleClass("open-dropdown");
    });
    // close profile dropdown when click outside of that
    $(document).click(function () {
        profileBlock.removeClass("open-dropdown");
    });

    $(profileBlock).click(function (e) {
        e.stopPropagation();
    });

    //header serach input toggle
    $(".header .search-box").on("click", function () {
        if ($(window).width() < 768) {
            $("body").toggleClass("search-input-open");
            // input being focused
            $(".header .search-box input").focus();
        } else {
            $("body").removeClass("search-input-open");
        }
    });

    $(".header .search-box input").on("click", function (e) {
        e.stopPropagation();
    });

    $(document).on("click", function (e) {
        const isInsideBox = $(e.target).closest(".search-box").length > 0;
        if (!isInsideBox) {
            $("body").removeClass("search-input-open");
        }
    });

    // Sidebar collapse
    $("#menuBtn").on("click", function () {
        $("#sidebar").toggleClass("collapsed");
        $("body").toggleClass("sidebar-collapsed");
        if ($(window).width() > 1199) {
            $(this).toggleClass("active");
        }
    });

    $(".sidebar .close-btn, .overlay").click(function (e) {
        e.stopPropagation();
        $(".sidebar").removeClass("collapsed");
        $("body").removeClass("sidebar-collapsed");
    });

    // Bottom dots menu toggle
    $(".profile").on("click", function (e) {
        e.stopPropagation();
        $(".bottom-menu").toggle();
    });

    // Click outside to close bottom menu
    $(document).on("click", function () {
        $(".bottom-menu").hide();
    });

    // for select2 dropdown
    $(".custom-select").select2({
        minimumResultsForSearch: Infinity, //to hide search
    });

    $(".custom-multi-select").select2({
        placeholder: "Select Role",
        closeOnSelect: false,
        minimumResultsForSearch: Infinity,
        templateSelection: function (data, container) {
            // hide built-in tags
            $(container).addClass("hide-option");
            return data.text;
        },
    });

    function updateMultiSelectCount(selectEl) {
        let selected = selectEl
            .find(":selected")
            .map(function () {
                return $(this).text();
            })
            .get();

        // join values with comma
        let finalText = selected.join(", ");

        // put text manually
        let container = selectEl.next(".select2-container");
        container.find(".select2-selection__rendered").text(finalText);

        // update count badge
        container.find(".select2-selection--multiple").attr("data-count", selected.length);
    }

    $(".custom-multi-select").on("change", function () {
        updateMultiSelectCount($(this));
    });

    // First load
    $(".custom-multi-select").each(function () {
        updateMultiSelectCount($(this));
    });

    $(window).scroll(function () {
        fixedHeader();
    });

    // window resize
    $(window).resize(function () {
        if ($("body").hasClass("search-input-open") && $(window).width() >= 768) {
            $("body").removeClass("search-input-open");
        }
    });
});
