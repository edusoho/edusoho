let $form = $('#bind-mobile-form');
let $smsCode = $('.js-sms-send');
let isOpenCode = false;

let validator = $form.validate({
  rules: {
    password: {
      required: true,
      es_remote: {
        type: 'post'
      },
    },
    mobile: {
      required: true,
      phone: true,
      es_remote: {
         type: 'get'
      },
    }
  },
  submitHandler: function (form) {
    if(isOpenCode) {
      form.submit();
    }
  }
});

$('#submit-btn').click(() => {
  if($form.validate().element($("#mobile"))) {
    isOpenCode = true;
    $smsCode.removeClass('disabled');
    initSmsCode();
  }else {
    isOpenCode = false;
    $('#sms-code').rules('remove');
  }
})

function initSmsCode() {
  $('#sms-code').rules('remove');
  $('#sms-code').rules('add', {
    required: true,
    unsigned_integer: true,
    smsCode: true,
    messages: {
      required: Translator.trans('请输入验证码'),
    }
  })
}