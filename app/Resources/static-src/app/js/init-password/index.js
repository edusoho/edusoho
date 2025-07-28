let $form = $('#init-password-form');

$.validator.addMethod('spaceNoSupport', function (value, element) {
  return value.indexOf(' ') < 0;
}, $.validator.format(Translator.trans('validate.have_spaces')));

let validator = $form.validate({
  rules: {
    newPassword: {
      required: true,
      spaceNoSupport: true,
      check_password_high: true,
    },
    confirmPassword: {
      required: true,
      equalTo: '#newPassword'
    }
  },
  messages: {
    newPassword: {
      required: Translator.trans('validate.check_password_high.message'),
      spaceNoSupport: Translator.trans('validate.check_password_high.message'),
      check_password_high: Translator.trans('validate.check_password_high.message'),
    }
  }
});

$('[type="submit"]').click(() => {
  if (validator.form()) {
    $form.submit();
  }
});

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