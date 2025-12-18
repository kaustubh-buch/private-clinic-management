
var countdownInterval = null;
var requestAnotherCode = commonWebsiteSettings.messages.request_another_code_message;
var $resendLink = $('#resend-link');
var countdownElement = $("#countdown-timer");

function countdownTimer(isajax = false,secondsLeft = 0){
    if(isajax){
        $resendLink = $('#resend-ajax-link');;
    }else{
        $resendLink = $('#resend-link');
    }
    $resendLink.css({
       'pointer-events': 'none',
       'cursor': 'not-allowed',
       'opacity': '0.5'
    });


    var countdownTime = parseInt(secondsLeft);

   countdownInterval = setInterval(function() {
       if (countdownTime > 0) {
           countdownElement.show().text(requestAnotherCode.replace(':seconds', countdownTime));
           countdownTime--;
       } else {
          clearTimer();
       }
   }, 1000);
}

function clearTimer(){
    if(countdownInterval){
        countdownElement.text("");
        $resendLink.css({
            'pointer-events': '',
            'cursor': '',
            'opacity': ''
        });
        clearInterval(countdownInterval);
    }
}
