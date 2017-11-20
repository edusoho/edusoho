import SmsSender from 'app/common/widget/sms-sender';
import { enterSubmit } from 'common/utils';
import Api from 'common/api';

const $form = $('#third-party-create-account-form');
const $btn = $('.js-submit-btn');
let captchaToken = null;

let validator = $form.validate({
  rules: {
    username: {
      required: true,
      byte_minlength: 4,
      byte_maxlength: 18,
      nickname: true,
      chinese_alphanumeric: true,
      es_remote: {
        type: 'get',
      }
    },
    password: {
      required: true,
      minlength: 5,
      maxlength: 20,
    },
    confirmPassword: {
      required: true,
      equalTo: '#password',
    },
    sms_code: {
      required: true,
      unsigned_integer: true,
    },
    captcha_code: {
      required: true,
      alphanumeric: true,
      captcha_checkout: true,
    }
  },
  messages: {
    sms_code: {
      required: Translator.trans('site.captcha_code.required'),
    }
  }
});

$.validator.addMethod('captcha_checkout', function(value, element, param) {
  let $element = $(element);
  let data = param.data ? param.data : { phrase: value };
  let isSuccess = 0;
  let params = {
    captchaToken: captchaToken
  }
  Api.captcha.validate({ data: data, params: params, async: false, promise: false }).done(res => {
    isSuccess = res.status;
    if (!isSuccess) {
      $.validator.messages.captcha_checkout = Translator.trans('图形验证码错误');
      initCaptchaCode();
    }
  }).error(res => {
    console.log(res);
  });
  return this.optional(element) || isSuccess;
}, Translator.trans('validate.captcha_checkout.message'));


const initCaptchaCode = () => {
  Api.captcha.get({ async: false, promise: false }).done(res => {
    $('#getcode_num').attr('src', res.image);
    captchaToken = res.captchaToken;
  }).error(res => {
    console.log('catch', res.responseJSON.error.message);
  });
}

window.onload = () => {
 initCaptchaCode();
}

$('#getcode_num').click((event) => {
  initCaptchaCode();
})


enterSubmit($form, $btn);

$btn.click((event) => {
  if (validator.form()) {
    window.location.href = $btn.data('url');
  }
});
