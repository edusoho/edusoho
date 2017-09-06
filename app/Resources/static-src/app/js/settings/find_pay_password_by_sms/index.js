import SmsSender from 'app/common/widget/sms-sender';
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
      required: Translator.trans('site.captcha_code.required'),
    }
  }
});

$('#submit-btn').click(() => {
  if (validator.form()) {
    $form.submit();
  }
})

$(".js-sms-send").click(()=>{
  var smsSender = new SmsSender({
    element: '.js-sms-send',
    url: $('.js-sms-send').data('smsUrl'),
    smsType: $('.js-sms-send').data('smsType'),
    preSmsSend: function () {
      var couldSender = true;
      return couldSender;
    }
  });
  $('.js-sms-send').off('click');
})