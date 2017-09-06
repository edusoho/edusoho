let $form = $('#pay-password-reset-update-form');

let validator = $form.validate({
  rules: {
    'form[currentUserLoginPassword]': {
      required: true,
    },
    'form[payPassword]': {
      required: true,
      minlength: 5,
      maxlength: 20
    },
    'form[confirmPayPassword]': {
      required: true,
      equalTo: '#form_payPassword'
    }
  },
  messages: {
    'form[currentUserLoginPassword]': {
      required: Translator.trans('user.settings.security.pay_password.set_login_password')
    },
    'form[payPassword]': {
      required: Translator.trans('user.settings.security.pay_password.set_new_pay_password')
    },
    'form[confirmPayPassword]': {
      required: Translator.trans('user.settings.security.pay_password.confirm_new_pay_password')
    }
  },
})

console.log(validator);

$('#payPassword-save-btn').on('click', (event) => {
  const $this = $(event.currentTarget);
  if (validator.form()) {
    $this.button('loading');
    $form.submit();
  }
})