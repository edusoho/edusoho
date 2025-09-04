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
      element: '[name="emailOrMobile"]',
      required: true,
      rule: 'email_or_mobile email_or_mobile_remote'
    });

    validator.addItem({
      element: '[name="nickname"]',
      rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:36} remote'
    });

    validator.addItem({
      element: '[name="password"]',
      required: true,
      rule: 'password_strong spaceNoSupport',
      errormessageRequired: '请设置8-32位包含字母大小写、数字、符号四种字符组合成的密码'
    });

    validator.addItem({
      element: '[name="confirmPassword"]',
      required: true,
      rule: 'confirmation{target:#password}'
    });

    $('.js-password-open-eye').on('click', function () {
      $('#password').attr('type', 'password');
      $('.js-password-open-eye').hide();
      $('.js-password-close-eye').show();
    });

    $('.js-password-close-eye').on('click', function () {
      $('#password').attr('type', 'text');
      $('.js-password-close-eye').hide();
      $('.js-password-open-eye').show();
    });

    $('.js-confirm-password-open-eye').on('click', function () {
      $('#confirmPassword').attr('type', 'password');
      $('.js-confirm-password-open-eye').hide();
      $('.js-confirm-password-close-eye').show();
    });

    $('.js-confirm-password-close-eye').on('click', function () {
      $('#confirmPassword').attr('type', 'text');
      $('.js-confirm-password-close-eye').hide();
      $('.js-confirm-password-open-eye').show();
    });
  };
});