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

$form.on('focusout.validate', (data, data2) => {
  if (!isSmsCode&& validator.form()) {
    $smsCode.removeClass('disabled');
    iniSmsCode();
    isSmsCode = false;
  } else if ( validator.form()) {
    isSmsCode = true;
  }
})

console.log(validator);

$('#submit-btn').click(() => {
  console.log(validator);
  if (isSmsCode && validator.form()) {
    $form.submit();
  }
})


function iniSmsCode() {
  $('#sms-code').rules('add', {
    required: true,
    unsigned_integer: true,
    remote: {
      url: $('#sms-code').data('url'),
      type: 'get',
      async: false,
      data: {
        'value': function () {
          return $('#sms-code').val();
        }
      }
    },
    messages: {
      required: Translator.trans('请输入验证码'),
    }
  })
}


