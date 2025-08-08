define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  var Notify = require('common/bootstrap-notify');

  exports.run = function () {
    var $form = $('#change-password-form');

    var validator = new Validator({
      element: '#change-password-form',
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return;
        }
        $('#change-password-btn').button('submiting').addClass('disabled');
        $.post($form.attr('action'), $form.serialize(), function (html) {

          var $modal = $('#modal');

          $.post($form.attr('action'), $form.serialize(), function (html) {
            $modal.modal('hide');
            Notify.success(Translator.trans('admin.user.password_modify_success_hint'));
          }).error(function () {
            Notify.danger(Translator.trans('admin.user.password_modify_error_hint'));
          });
        });
      }
    });

    Validator.addRule('spaceNoSupport', function (options) {
      let value = $(options.element).val();
      return value.indexOf(' ') < 0;
    }, Translator.trans('validate.have_spaces'));

    validator.addItem({
      element: '[name="newPassword"]',
      required: true,
      rule: 'check_password_high spaceNoSupport',
      errormessageRequired: Translator.trans('validate.check_password_high.message')
    });

    validator.addItem({
      element: '[name="confirmPassword"]',
      required: true,
      rule: 'confirmation{target:#newPassword}'
    });

  };

  $('.js-new-password-open-eye').on('click', function () {
    $('#newPassword').attr('type', 'password');
    $('.js-new-password-open-eye').hide();
    $('.js-new-password-close-eye').show();
  })

  $('.js-new-password-close-eye').on('click', function () {
    $('#newPassword').attr('type', 'text');
    $('.js-new-password-close-eye').hide();
    $('.js-new-password-open-eye').show();
  })

  $('.js-confirm-password-open-eye').on('click', function () {
    $('#confirmPassword').attr('type', 'password');
    $('.js-confirm-password-open-eye').hide();
    $('.js-confirm-password-close-eye').show();
  })

  $('.js-confirm-password-close-eye').on('click', function () {
    $('#confirmPassword').attr('type', 'text');
    $('.js-confirm-password-close-eye').hide();
    $('.js-confirm-password-open-eye').show();
  })

});