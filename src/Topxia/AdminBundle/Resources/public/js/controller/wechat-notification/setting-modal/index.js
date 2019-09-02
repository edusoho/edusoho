define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');

  exports.run = function() {
    $('.js-wechat-notification-setting').on('click', function() {
      var $this = $(this);
      var $parent = $this.parent();
      var isEnable = $this.val();
      var url = $this.data('url');
      var reverseEnable = isEnable == 1 ? 0 : 1;

      // $.post(url, {'isEnable':reverseEnable})
      // .success(function(response) {
      //   if ($parent.hasClass('checked')) {
      //     $parent.removeClass('checked');
      //   } else {
      //     $parent.addClass('checked');
      //   }
      //   $this.val(reverseEnable);
      //   if ($this.data('key') === 'homeworkOrTestPaperReview' || 'courseRemind') {
      //     window.location.reload();
      //   }
      // }).fail(function (xhr, status, error){
      //   Notify.danger(xhr.responseJSON.error.message);
      // })
    });
  };
});