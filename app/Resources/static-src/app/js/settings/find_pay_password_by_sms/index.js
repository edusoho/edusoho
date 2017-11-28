import SmsSender from 'app/common/widget/sms-sender';

let smsSend = '.js-sms-send';
let $smsCode = $(smsSend);
let $form = $('#settings-find-pay-password-form');
let validator = $form.validate({
  rules: {
    sms_code: {
      required: true,
      unsigned_integer: true,
      es_remote: true,
    }
  },
  messages: {
    sms_code: {
      required: Translator.trans('site.captcha_code.required'),
    }
  }
});

$('#submit-btn').click(() => {
  if (validator.form()) {
    $form.submit();
  }
})

$smsCode.on('click', () => {
  new SmsSender({
    element: smsSend,
    url: $smsCode.data('smsUrl'),
    smsType: $smsCode.data('smsType'),
    preSmsSend: () => {
      return true;
    }
  });
})