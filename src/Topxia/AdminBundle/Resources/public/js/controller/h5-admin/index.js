define(function(require, exports, module) {
  "use strict";
  exports.run = function() {
    $('.js-site-save').click(function() {
      $('[name="template"]').val($(this).data('template'));
      $('[name="version"]').val($(this).data('version'));
      $('#wap-setting-form').submit();
    });

    $(".old-site-set").click(function(){
      $('.setting-nav-tab a[href="#theme-page"]').tab('show');
    });
    
    var href = window.location.href.split('#');
    $('.setting-nav-tab a[href="#'+href[1]+'"]').tab('show');

    $('.js-site-edit').click(function () {
      $('#find-page').html('<iframe src="/h5/admin/index.html" frameborder="0" style="width: 100%;height: 700px;border: none;"></iframe>');
    });
  };
});

