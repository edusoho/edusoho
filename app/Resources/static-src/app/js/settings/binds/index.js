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
      intro: `<div class="wechat-intro-title cd-text-warning"><i class="es-icon es-icon-xinxi cd-mr8"></i>提示</div>
      <div class="wechat-intro-content">
        <div>为享受更好的服务，建议您开启微信课程通知。</div>
        <div>点这里绑定微信，并关注服务号即可开启课程通知。</div>
      </div>`,
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
if (!store.get(WECHAT_BIND_INTRO) && $('.wechat-inform-section').length <= 0 && $notificationEnable) {
  store.set(WECHAT_BIND_INTRO, true);
  wechatIntro();
}
