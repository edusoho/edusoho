define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {
    $('.js-switch-input').click(function() {
      var $this = $(this);
      var setting = $this.val();
      var $parent = $this.parent();
      setting = setting == 1 ? 0 : 1;
      $this.val(setting);
      if ($parent.hasClass('checked')) {
        if (!confirm('您确定要关闭云监考吗？')) {
          return false;
        }
      }
      $.post($this.data('url'),{'enabled':setting},function(data) {
        if ($parent.hasClass('checked')) {
          $parent.removeClass('checked');
        } else {
          $parent.addClass('checked');
        }
      });
    });
  };

});
