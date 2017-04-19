let $form = $('#settings-find-pay-password-form');
let validator = $form.validate({
  rules: {
    sms_code: {
      required: true,
      unsigned_integer: true,
      smsCode: true,
    }
  },
  messages: {
    sms_code: {
      required: Translator.trans('请输入验证码'),
    }
  }
});

$('#submit-btn').click(() => {
  if (validator.form()) {
    $form.submit();
  }
})