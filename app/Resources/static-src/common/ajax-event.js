import RewardPointNotify from 'app/common/reward-point-notify';
import notify from 'common/notify';

let rpn = new RewardPointNotify();
let $document = $(document);
$document.ajaxSuccess(function(event, XMLHttpRequest, ajaxOptions){
  rpn.push(XMLHttpRequest.getResponseHeader('Reward-Point-Notify'));
  rpn.display();
});

$document.ajaxError(function (event, jqxhr, settings, exception) {
  let json = jQuery.parseJSON(jqxhr.responseText);
  let error = json.error;
  if (!error) {
    return;
  }
  let ua = navigator.userAgent.toLowerCase();
  
  if (!error.code) {
    return;
  }
  
  switch(error.code)
  {
  case 4500105:
    window.location.href = '/login';
    break;
  case 4040100:
    if (ua.match(/micromessenger/i) == 'micromessenger' && $('meta[name=is-open]').attr('content') != 0) {
      window.location.href = '/login/bind/weixinmob?_target_path=' + location.href;
    } else {
      let $loginModal = $('#login-modal');
      $('.modal').modal('hide');
      $loginModal.modal('show');
      $.get($loginModal.data('url'), function (html) {
        $loginModal.html(html);
      });
    }
    break;
  default:
    notify('danger', error.message);
  }
});

$document.ajaxSend(function (a, b, c) {
  // 加载loading效果
  let url = c.url;
  url = url.split('?')[0];
  let $dom = $(`[data-url="${url}"]`);
  if ($dom.data('loading')) {
    let loading;
    loading = cd.loading({
      isFixed: $dom.data('is-fixed')
    });

    let loadingBox = $($dom.data('target') || $dom);
    loadingBox.html(loading);
  }

  if (c.type === 'POST') {
    b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
  }
});