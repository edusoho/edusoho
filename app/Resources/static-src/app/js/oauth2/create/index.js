import SmsSender from 'app/common/widget/sms-sender';
import { enterSubmit } from 'common/utils';

const $form = $('#third-party-create-account-form');
const $btn = $('.js-submit-btn');

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
      es_remote: true,
    }
  },
  messages: {
    sms_code: {
      required: Translator.trans('site.captcha_code.required'),
    }
  }
});

enterSubmit($form, $btn);

$btn.click((event) => {
  if (validator.form()) {
    window.location.href = $btn.data('url');
  }
});
