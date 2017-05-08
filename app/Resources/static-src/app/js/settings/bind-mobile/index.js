let $form = $('#bind-mobile-form');
let $smsCode = $('.js-sms-send');

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
        type: 'get',
        callback: (bool) => {
          if (bool) {
            $smsCode.removeClass('disabled');
          }
          else {
            $smsCode.addClass('disabled');
          }
        }
      },
    },
    sms_code: {
      required: true,
      unsigned_integer: true,
      es_remote: {
        type: 'get',
      },
    },
  },
  messages: {
    sms_code: {
      required: Translator.trans('请输入验证码')
    }
  }
});