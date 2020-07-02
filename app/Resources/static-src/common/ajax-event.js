import RewardPointNotify from 'app/common/reward-point-notify';

let $loginModal = $('#login-modal');
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
  let message =  error.code ? error.message : Translator.trans('site.service_error_hint');
  switch(error.code)
  {
  case 4030102:
    window.location.href = '/login';
    break;
  case 4040101:
    if($('meta[name=wechat_login_bind]').attr('content') != 0) {
      window.location.href = '/login';
    } else {
      $('.modal').modal('hide');
      $loginModal.modal('show');
      $.get($loginModal.data('url'), function (html) {
        $loginModal.html(html);
      });
    }
    break;
  default:
    cd.message({
      type: 'danger',
      message: message
    });
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
    b.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  }
});