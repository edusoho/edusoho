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
          Notify.success(Translator.trans('admin.user.create_new_user_success_hint'));
          window.location.reload();
        }).error(function () {
          Notify.danger(Translator.trans('admin.user.create_new_user_fail_hint'));
        });

      }
    });

    Validator.addRule("spaceNoSupport", function (options) {
      let value = $(options.element).val();
      return value.indexOf(' ') < 0;
    }, Translator.trans('validate.have_spaces'));

    validator.addItem({
      element: '[name="emailOrMobile"]',
      required: true,
      rule: 'email_or_mobile email_or_mobile_remote'
    });

    validator.addItem({
      element: '[name="nickname"]',
      rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:18} remote'
    });

    let passwordLevel = $('#password_level').val();

    validator.addItem({
      element: '[name="password"]',
      required: true,
      rule: 'check_password_' + passwordLevel + ' spaceNoSupport'
    });

    validator.addItem({
      element: '[name="confirmPassword"]',
      required: true,
      rule: 'confirmation{target:#password}'
    });
  };

});