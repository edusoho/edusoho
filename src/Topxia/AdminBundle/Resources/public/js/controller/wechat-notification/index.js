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

    $('.js-wechat-notification-message-subscribe-setting').on('click', function() {
      if ($('.js-click-enable').length > 0) {
        Notify.danger(Translator.trans('wechat.notification.cloud_open_tip'));
      }
      if ($('.js-wechat-notification-message-subscribe-authorization').data('enabled') == '0') {
        Notify.danger(Translator.trans('admin.wechat_notification.message_subscribe.disable_setting_tip'));
      }
    });

    $('.js-wechat-pre-auth-url').on('click', function (event) {
      $.get($('.js-wechat-pre-auth-url').data('url'), function (resp) {
        $('.js-re-auth-btn').attr('href', resp.url);
        $('#confirm-modal').modal('show');
        window.open(resp.url,'_blank');
      });
    });
    $('#confirm-modal').on('hidden.bs.modal', function () {
      window.location.reload();
    });

    $('.js-notification-submit').click(function () {
      $.post($form.data('url'), $form.serialize())
        .success(function() {
          Notify.success(Translator.trans('site.save_success_hint'));
          window.location.reload();
        }).fail(function (){
          Notify.danger(Translator.trans('site.save_error_hint'));
        });
    });

    $('input[type=radio][name=notificationType]').change(function() {
      var type = $('input[type=radio][name=notificationType]:checked').val();
      if (type === 'messageSubscribe') {
        $('#message-subscribe').show();
        $('#service-follow').hide();
        $('#message-subscribe-form').show();
        $('#message-template-tip').hide();
      }
      if (type === 'serviceFollow') {
        $('#service-follow').show();
        $('#message-subscribe').hide();
        $('#message-subscribe-form').hide();
        $('#message-template-tip').show();
      }
    });
  };
});