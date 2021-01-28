import { countDown } from 'app/common/count-down';

let $message = $('#page-message-container');
let gotoUrl = $message.data('goto');
let duration = $message.data('duration');
let os = $message.data('os');
let token = $message.data('token');
let $countDown = $('.js-count-down');
let h5GotoUrl = $message.data('h5Goto');

if (os === 'iOS') {
  window.webkit.messageHandlers.login.postMessage(token);
} else if (os === 'Android') {
  window.android.login(token);
} else {
  gotoUrl = h5GotoUrl;
  if (duration > 0 && gotoUrl) {
    countDown($countDown, duration, gotoUrl);
  }
}
