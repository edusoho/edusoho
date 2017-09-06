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
      required: '请输入用户登录密码'
    },
    'form[payPassword]': {
      required: '请输入新的支付密码',
    },
    'form[confirmPayPassword]': {
      required: '请确认输入的支付密码',
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