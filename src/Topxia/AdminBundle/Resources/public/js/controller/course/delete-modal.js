define(function (require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {

    var $form = $('#delete-form');
    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return false;
        }
        $('.js-delete-btn').button('loading');
        $.post($form.attr('action'), $form.serialize(), function (response) {
          if (response.success) {
            $.post($('#delete-btn').data('url'), function (resp) {
              if (resp.code == 0) {
                Notify.success(Translator.trans('admin.course.delete_success_hint'));
                location.reload();
              } else {
                Notify.success(Translator.trans('admin.course.delete_failed_hint') + '：' + resp.message);
              }
            });
          } else {
            $('.js-delete-btn').button('reset');
            $('#delete-form').children('div').addClass('has-error');
            $('#delete-form').find('.help-block').show().text(Translator.trans('admin.course.delete_course.check_password_fail_hint'));
          }
        });
      }
    });

    validator.addItem({
      element: '[name=password]',
      required: true,
      rule: 'minlength{min:5} maxlength{max:20}',
      display: Translator.trans('admin.course.validate_old.password_required_hint')
    });
  };
});
