$(document).ready(function () {
  hideLoader();
  $.validator.setDefaults({
    errorElement: "p",
    errorClass: "error-message",
    highlight: function (element) {
      $(element).addClass("is-invalid");
    },
    unhighlight: function (element) {
      $(element).removeClass("is-invalid");
    },
  });
  $.validator.addMethod(
      "strongPassword",
      function (value) {
          return validatePasswordStrength(value);
      },
      "{{ __('messages.validation.strong_password') }}"
  );
  const passwordInput = document.getElementById("newPassword");
  const tips = document.getElementById("passwordSuggestions");

  if (passwordInput && tips) {
    passwordInput.addEventListener("focus", () => {
      // Add styles
      tips.style.position = "absolute";
      tips.style.width = "100%";
      tips.style.zIndex = "9";
      tips.classList.remove("hidden");
    });

    passwordInput.addEventListener("blur", () => {
      // Remove styles
      tips.style.position = "";
      tips.style.width = "";
      tips.style.zIndex = "";
      tips.classList.add("hidden");
    });
  }
});
function validatePasswordStrength(pass) {
  // Conditions
  const hasMinLength = pass.length >= 8;
  const hasUpperCase = /[A-Z]/.test(pass);
  const hasNumber = /[0-9]/.test(pass);
  const hasSpecialChar = /[!@#$%^&*\.]/.test(pass);
  return hasMinLength && hasUpperCase && hasNumber && hasSpecialChar;
}
function handleBackendValidationErrors(formSelector, errors) {
    const validator = $(formSelector).validate();
    $.each(errors, function (field, messages) {
        validator.showErrors({
            [field]: messages[0],
        });
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