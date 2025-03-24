define(function (require, exports, module) {
  let Validator = require('bootstrap.validator');
  let Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {
    let $modal = $('#user-create-form').parents('.modal');
    let validator = new Validator({
      element: '#user-create-form',
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return false;
        }

        $('#user-create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize(), function (html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin.user.create_new_staff_success_hint'));
          window.location.reload();
        }).error(function (response) {
          if (response.responseJSON){
            Notify.danger(response.responseJSON.error.message);
          }else {
            Notify.danger(Translator.trans('admin.user.create_new_staff_fail_hint'));
          }
          $('#user-create-btn').button('reset').removeClass('disabled');
        });

      }
    });
    Validator.addRule('spaceNoSupport', function (options) {
      let value = $(options.element).val();
      return value.indexOf(' ') < 0;
    }, Translator.trans('validate.have_spaces'));

    validator.addItem({
      element: '[name="email"]',
      required: true,
      rule: 'email email_remote'
    });

    validator.addItem({
      element: '[name="nickname"]',
      required: true,
      rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:36} remote'
    });

    validator.addItem({
      element: '[name="password"]',
      required: true,
      rule: 'check_password_high spaceNoSupport',
    });

    validator.addItem({
      element: '[name="confirmPassword"]',
      required: true,
      rule: 'confirmation{target:#password}'
    });
  };

});