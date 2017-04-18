import SmsSender from 'app/common/widget/sms-sender';
let $form = $('#bind-mobile-form');
let smsSender = new SmsSender($form);
let $smsCode = $('.js-sms-send');

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

function iniSmsCode() {
  $('#sms-code').rules('add', {
    required: true,
    unsigned_integer: true,
    remote: {
      url: $('#sms_code').data('url'),
      type: 'get',
      async: false,
      data: {
        'value': function () {
          return $('#sms_code').val();
        }
      }
    },
    messages: {
      required: Translator.trans('请输入短信验证码')
    }
  })
}


$form.on('focusout.validate', (data, data2) => {
  if ($smsCode.hasClass('disabled') && validator.form()) {
    $smsCode.removeClass('disabled');
    iniSmsCode();
  }else {
    $smsCode.addClass('disabled');
    $('#sms-code').rules('remove');
  }
})

$('.js-sms-send').click(() => {
  if ($form.valid()) {
    console.log('valid');
  }
})


$('#submit-btn').click(() => {
  if ($smsCode.hasClass('disabled') && validator.form()) {
    $smsCode.removeClass('disabled');
    iniSmsCode();
  } else if (validator.form()) {
    $form.submit();
  }
})
