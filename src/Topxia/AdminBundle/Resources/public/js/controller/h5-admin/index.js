define(function(require, exports, module) {
  "use strict";
  exports.run = function() {
    $('.js-site-save').click(function() {
      $('[name="template"]').val($(this).data('template'));
      $('[name="version"]').val($(this).data('version'));
      $('#wap-setting-form').submit();
    });

    $('.js-site-edit').click(function () {
      $('#find-page').html('<iframe src="/h5/admin/index.html" frameborder="0" style="width: 100%;height: 700px;border: none;"></iframe>');
    });
  };
});

