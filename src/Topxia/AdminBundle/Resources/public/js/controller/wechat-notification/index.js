define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');

  exports.run = function() {
    $('.js-wechat-notification-setting').on('click', function() {
      if ($('.js-click-enable').length > 0) {
        Notify.danger(Translator.trans('wechat.notification.cloud_open_tip'));
        return;
      }
    });
  };
});