define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  let $form = $('#notification-form');


  exports.run = function() {
    $('.js-wechat-notification-setting').on('click', function() {
      if ($('.js-click-enable').length > 0) {
        Notify.danger(Translator.trans('wechat.notification.cloud_open_tip'));
      }
      if ($('.js-wechat-template-list').data('enabled') == '0') {
        Notify.danger(Translator.trans('未开启微信服务号通知'));
      }
    });

    $('.js-notification-submit').click(function () {
      $.post($form.data('url'), $form.serialize())
        .success(function() {
          Notify.success(Translator.trans('site.save_success_hint'));
        }).fail(function (){
          Notify.danger(Translator.trans('site.save_error_hint'));
      });
    });

    $('input[type=radio][name=notification_type]').change(function() {
      var type = $('input[type=radio][name=notification_type]:checked').val();

      if (type === 'MessageSubscribe') {
        $('#message-subscribe').show()
        $('#service-follow').hide()
        $('#message-subscribe-form').show()
      }
      if (type === 'serviceFollow') {
        $('#service-follow').show()
        $('#message-subscribe').hide()
        $('#message-subscribe-form').hide()
      }
    });
  };
});