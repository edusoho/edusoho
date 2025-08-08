let $form = $('#password-reset-update-form');
let validator = $form.validate({
  rules: {
    'form[password]': {
      check_password_high: true,
    },
    'form[confirmPassword]': {
      required: true,
      equalTo: '#form_password'
    }
  },
  messages: {
    'form[password]': {
      required: Translator.trans('validate.check_password_high.message'),
    }
  }
});

$('.js-password-open-eye').on('click', function () {
  $('#form_password').attr('type', 'password');
  $('.js-password-open-eye').hide();
  $('.js-password-close-eye').show();
})

$('.js-password-close-eye').on('click', function () {
  $('#form_password').attr('type', 'text');
  $('.js-password-close-eye').hide();
  $('.js-password-open-eye').show();
})

$('.js-confirm-password-open-eye').on('click', function () {
  $('#form_confirmPassword').attr('type', 'password');
  $('.js-confirm-password-open-eye').hide();
  $('.js-confirm-password-close-eye').show();
})

$('.js-confirm-password-close-eye').on('click', function () {
  $('#form_confirmPassword').attr('type', 'text');
  $('.js-confirm-password-close-eye').hide();
  $('.js-confirm-password-open-eye').show();
})