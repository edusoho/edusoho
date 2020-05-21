define(function (require, exports, module) {
  var Notify = require('common/bootstrap-notify');

  exports.run = function () {
    $('.js-generate-btn').click(function() {
      $.post($('.js-generate-btn').data('url'),function(data) {
        $('.js-capture-url').html(data);
        $("#content").val(data);
      });
    });
    $('#copy').click(function(){
      $("#content").select();
      document.execCommand("Copy");
      Notify.success(Translator.trans('notify.copy_link_success'));
    });
  };

});
