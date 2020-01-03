define(function(require, exports, module) {

  let Validator = require('bootstrap.validator');
  let Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function() {
    let $form = $('#category-form');
    let $modal = $form.parents('.modal');

    let validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return ;
        }

        $('#category-create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize()).done(function(html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin.category.save_success_hint'));
          window.location.reload();
        }).fail(function() {
          Notify.danger(Translator.trans('admin.category.save_fail_hint'));
        });
      }
    });

    validator.addItem({
      element: '#category-name-field',
      required: true,
      rule: 'maxlength{max:30} visible_character'
    });
  };

});