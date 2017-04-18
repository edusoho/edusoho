import SmsSender from 'app/common/widget/sms-sender';
let $form = $('#bind-mobile-form');
let smsSender = new SmsSender($form);
let $smsCode = $('.js-sms-send');
let isSmsCode = false;

let validator = $form.validate({
  rules: {
    password: {
      required: true,
      remote: {
        url: $('#password').data('url'),
        type: 'post',
        async: false,
        data: {
          'value': function () {
            return $('#password').val();
          }
        }
      }
    },
    mobile: {
      required: true,
      phone: true,
      remote: {
        url: $('#mobile').data('url'),
        type: 'get',
        async: false,
        data: {
          'value': function () {
            return $('#mobile').val();
          }
        }
      }
    }
  },
});

$('#submit-btn').click(() => {
  if (!isSmsCode&& validator.form()) {
    $smsCode.removeClass('disabled');
    iniSmsCode();
    isSmsCode = false;
  } else if ( validator.form()) {
    isSmsCode = true;
  }
})


function iniSmsCode() {
  $('#sms-code').rules('add', {
    required: true,
    unsigned_integer: true,
    smsCode: true,
    messages: {
      required: Translator.trans('请输入验证码'),
    }
  })
}


