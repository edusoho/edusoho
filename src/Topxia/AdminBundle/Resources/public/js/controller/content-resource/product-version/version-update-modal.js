define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');

  exports.run = function() {
    var $form = $('#version-update-form');
    $('#version-update-btn').click(function () {
      $(this).html($(this).data('loadingText'));
      $(this).addClass('disabled');
      $.post($form.attr('action'), $form.serialize(), function(res) {
        if (true === res.status) {
          Notify.success(Translator.trans('merchant.resource.product_version.update_success_hint'), 1);
        } else {
          Notify.danger(res.error ? res.error : Translator.trans('merchant.resource.product_version.update_failed_hint') , 1);
        }
        setTimeout('window.location.reload();', 1000);
      });
    });
  };

});