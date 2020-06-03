define(function(require, exports, module) {
  "use strict";
  exports.run = function() {
    $('.js-site-save').click(function() {
      $('[name="template"]').val($(this).data('template'));
      $('[name="version"]').val($(this).data('version'));
      $('#wap-setting-form').submit();
    });
  };
});

