import 'store';
const WECHAT_BIND_INTRO = 'WECHAT_BIND_INTRO';

$('.js-unbind-btn').on('click', function() {
  let $this = $(this);
  let url = $this.data('url');

  cd.confirm({
    title: Translator.trans('user.settings.unbind_title'),
    content: Translator.trans('user.settings.unbind_content'),
    okText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.close'),
  }).on('ok', () => {
    $.post(url, function (data) {
      cd.message({type: 'success', message: Translator.trans(data.message)});
      setTimeout(function() {
        window.location.reload();
      }, 3000);
    });
  });
});

const wechatIntro = () => {
  const doneLabel = '<i class="es-icon es-icon-close01"></i>';
  const customClass = 'wechat-intro-intro';
  introJs().setOptions({
    steps: [{
      element: '.js-wechat-btn',
      intro: Translator.trans('wechat.notification.wechat_bind_hover'),
    }],
    skipLabel: doneLabel,
    doneLabel: doneLabel,
    showBullets: false,
    tooltipPosition: 'down',
    showStepNumbers: false,
    exitOnEsc: false,
    exitOnOverlayClick: false,
    tooltipClass: customClass
  }).start();
}

var $notificationEnable = $('#wechat_notification_enabled').val();
if (!store.get(WECHAT_BIND_INTRO) && $('#wechat_notification_enabled').data('status') !== 'bind' && $notificationEnable) {
  store.set(WECHAT_BIND_INTRO, true);
  wechatIntro();
}

let $target = $("#wechat-login-qrcode");
if (typeof($target.data('url')) != 'undefined') {
  $.get($target.data('url'), res => {
    $target.attr('src', res.img);
  });
}

