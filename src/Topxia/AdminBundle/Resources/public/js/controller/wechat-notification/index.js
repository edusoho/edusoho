define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');

  exports.run = function() {
    $('[data-toggle="switch"]').on('click', function() {
      if ($('.js-click-enable').length > 0) {
        Notify.danger(Translator.trans('wechat.notification.cloud_open_tip'));
        return;
      }
      var $this = $(this);
      var $parent = $this.parent();
      var isEnable = $this.val();
      var url = $this.data('url');
      var reverseEnable = isEnable == 1 ? 0 : 1;

      $.post(url, {'isEnable':reverseEnable})
      .success(function(response) {
        if ($parent.hasClass('checked')) {
          $parent.removeClass('checked');
        } else {
          $parent.addClass('checked');
        }
        $this.val(reverseEnable);
      }).fail(function (xhr, status, error){
        Notify.danger(xhr.responseJSON.error.message);
      })
    });
  };
});