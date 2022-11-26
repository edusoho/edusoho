define(function (require, exports, module) {
  "use strict";
  exports.run = function () {
    var href = window.location.href.split('#');
    $('.js-site-save').click(function () {
      $('[name="template"]').val($(this).data('template'));
      $('[name="version"]').val($(this).data('version'));
      $.post(href[0], $('#wap-setting-form').serialize(), function () {
        window.location.href = href[0];
      });
    });

    $(".old-site-set").click(function () {
      $('.setting-nav-tab a[href="#theme-page"]').tab('show');
    });

    $('.setting-nav-tab a[href="#' + href[1] + '"]').tab('show');

    $('.js-site-edit').click(function () {
      $('#find-page').html('<iframe src="/h5/admin/index.html" frameborder="0" style="width: 100%;height: 700px;border: none;"></iframe>');
    });
  };
});

