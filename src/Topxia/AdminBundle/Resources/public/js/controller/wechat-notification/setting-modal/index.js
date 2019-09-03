define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');

  exports.run = function() {
    $('.js-notification-setting-btn').on('click', function() {
      var $this = $(this);
      var url = $this.data('url');
      $this.button('loading');
      $.post(url, $('#notification-setting-form').serialize())
        .success(function(response) {
          window.location.reload();
        }).fail(function (xhr, status, error){
          $this.button('reset');
          Notify.danger(xhr.responseJSON.error.message);
        });
    });
  };
});