define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  exports.run = function() {
    let $form = $('#apple-form');
    $('.js-setting-submit').click(function () {
      $.post($form.data('url'), $form.serialize())
        .success(function(response) {
          Notify.success(Translator.trans('site.save_success_hint'));
        }).fail(function (xhr, status, error){
          Notify.danger(xhr.responseJSON.error.message);
        });
    });
  };
});