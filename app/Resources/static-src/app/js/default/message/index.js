let $message = $("#page-message-container");
let gotoUrl = $message.data('goto');
let duration = $message.data('duration');
let isApp = $message.data('isApp');
let token = $message.data('token');

if (isApp) {
  nativeApp.login(token);
} else {
  if (duration > 0 && gotoUrl) {
    setTimeout(function () {
      window.location.href = gotoUrl;
    }, duration);
  }
}




