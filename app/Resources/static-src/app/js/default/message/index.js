let $message = $('#page-message-container');
let gotoUrl = $message.data('goto');
let duration = $message.data('duration');
if (duration > 0 && gotoUrl) {
  setTimeout(function () {
    window.location.href = gotoUrl;
  }, duration);
}

