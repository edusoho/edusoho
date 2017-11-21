let $message = $("#page-message-container");
let gotoUrl = $message.data('goto');
let duration = $message.data('duration');
let os = $message.data('os');
let token = $message.data('token');

if ('iOS' === os) {
  window.webkit.messageHandlers.login.postMessage(token);
} else if ('Android' === os) {
  window.android.login(token);
} else {
  if (duration > 0 && gotoUrl) {
    setTimeout(function () {
      window.location.href = gotoUrl;
    }, duration);
  }
}




