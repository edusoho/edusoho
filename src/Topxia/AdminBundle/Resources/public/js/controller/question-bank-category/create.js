define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);
    
  exports.run = function() {
    var $form = $('#category-form');
    var $modal = $form.parents('.modal');

    var validator = new Validator({
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
      rule: 'maxlength{max:30}'
    });

  };

});