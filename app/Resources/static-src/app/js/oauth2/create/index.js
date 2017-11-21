import { enterSubmit } from 'common/utils';
import notify from 'common/notify';
import Api from 'common/api';

const $form = $('#third-party-create-account-form');
const $btn = $('.js-submit-btn');
const $smsCode = $('.js-sms-send');
const $timeLeft = $('.js-time-left');
const $fetchBtnText = $('.js-fetch-btn-text');

let captchaToken = null;
let smsToken = null;
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
      captcha_checkout: {
        callback: function(bool) {
          if (bool) {
            $smsCode.removeAttr('disabled');
          } else {
            $smsCode.attr('disabled', true);
          }
        }
      }
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
  let callback = param.callback ? param.callback : null;
  let isSuccess = 0;
  let params = {
    captchaToken: captchaToken
  }
  Api.captcha.validate({ data: data, params: params, async: false, promise: false }).done(res => {
    if (res.status === 'success') {
      isSuccess = true;
    } else if (res.status === 'expired') {
      isSuccess = false;
      $.validator.messages.captcha_checkout = Translator.trans('图形验证码已过期');
      initCaptchaCode();
    } else {
      isSuccess = false;
      console.log('表单提交的验证');
      $.validator.messages.captcha_checkout = Translator.trans('图形验证码错误');
      initCaptchaCode();
    }
    if (callback) {
      callback(isSuccess);
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

$smsCode.click((event) => {
  const $target = $(event.target);
  let data = {
    type: 'register',
    mobile: $('.js-account').html(),
    captchaToken: captchaToken,
    phrase: $('#captcha_code').val()
  };
  Api.sms.send({ data: data }).then((res) => {
    smsToken = res.smsToken;
    showCountDown();
  }).catch((res) => {
    const code = res.responseJSON.error.code;
    switch(code) {
      case 30001:
        notify('danger', Translator.trans('oauth.refresh_captcha_code_tip'));
        $('#captcha_code').val('');
        $target.attr('disabled', true);
        initCaptchaCode();
        break;
      case 30002:
        notify('danger', Translator.trans('oauth.send_error_message_tip'));
        break;
      case 30003:
        notify('danger', Translator.trans('admin.site.cloude_sms_enable_hint'));
        break;
      default:
        notify('danger', Translator.trans('site.data.get_sms_code_failure_hint'));
        break;
    }
  });
})

const showCountDown = () => {
  $timeLeft.html('10');
  $fetchBtnText.html(Translator.trans('site.data.get_sms_code_again_btn'));
  notify('success', Translator.trans('site.data.get_sms_code_success_hint'));
  refreshTimeLeft();
}

const refreshTimeLeft = () => {
  let leftTime = $timeLeft.text();
  $timeLeft.html(leftTime - 1);
  if (leftTime - 1 > 0) {
    $smsCode.attr('disabled', true);
    setTimeout(refreshTimeLeft, 1000);
  } else {
    $timeLeft.html('');
    $fetchBtnText.html(Translator.trans('site.data.get_sms_code_btn'));
    $smsCode.removeAttr('disabled');
  }
}

// 刷新二维码
window.onload = () => {
 initCaptchaCode();
}

$('#getcode_num').click((event) => {
  initCaptchaCode();
})

// 提交表单

enterSubmit($form, $btn);

$btn.click((event) => {
  $('#captcha_code').rules('remove', 'captcha_checkout');
  if (validator.form()) {
    let data = {
      smsToken: smsToken,
      mobile: $('.js-account').html(),
      smsCode: $('#sms-code').val(),
      captchaToken: captchaToken,
      phrase: $('#captcha_code').val()
    }
    console.log(data);
    $.post($btn.data('url'), data, (response) => {
      console.log(response);
      if (response.success === 1) {
        window.location.href = response.url;
      } else {
        $('#captcha_code').rules('add', {
          captcha_checkout: true
        });
        if (!$('.js-password-error').length) {
          $btn.prev().addClass('has-error').append(`<p id="password-error" class="form-error-message js-password-error">您输入的短信验证码不正确</p>`);
        }
      }
    })
  }
});
