import { enterSubmit } from 'app/common/form';
import notify from 'common/notify';
import { countDown } from './count-down';
import Api from 'common/api';


export default class Create {
  constructor() {
    this.$form = $('#third-party-create-account-form');
    this.$btn = $('.js-submit-btn');
    this.validator = null;
    this.captchaToken = null;
    this.smsToken = null;

    this.init();
  }

  init() {
    this.initCaptchaCode();
    this.initValidator();
    this.changeCaptchaCode();
    this.sendMessage();
    this.submitForm();
    this.removeSmsErrorTip();
  }

  initValidator() {
    const self = this;
    const $smsCode = $('.js-sms-send');
    this.validator = this.$form.validate({
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
        captcha_code: {
          required: true,
          alphanumeric: true,
          captcha_checkout: {
            callback: function(bool) {
              if (bool) {
                $smsCode.removeAttr('disabled');
              } else {
                $smsCode.attr('disabled', true);
                let changeToken = self.initCaptchaCode();
                self.captchaToken = changeToken;
                return self.captchaToken;
              }
            }
          }
        },
        sms_code: {
          required: true,
          unsigned_integer: true,
          rangelength: [6, 6],
        },
      },
      messages: {
        sms_code: {
          required: Translator.trans('site.captcha_code.required'),
          rangelength: Translator.trans('validate.sms_code.message')
        }
      }
    });

    $.validator.addMethod('captcha_checkout', function(value, element, param) {
      let $element = $(element);
      if (value.length < 5) {
        $.validator.messages.captcha_checkout = Translator.trans('oauth.captcha_code_length_tip');
        return;
      }
      let data = param.data ? param.data : { phrase: value };
      let callback = param.callback ? param.callback : null;
      let isSuccess = 0;
      let params = {
        captchaToken: self.captchaToken
      }
      Api.captcha.validate({ data: data, params: params, async: false, promise: false }).done(res => {
        if (res.status === 'success') {
          isSuccess = true;
        } else if (res.status === 'expired') {
          isSuccess = false;
          $.validator.messages.captcha_checkout = Translator.trans('oauth.captcha_code_expired_tip');
        } else {
          isSuccess = false;
          $.validator.messages.captcha_checkout = Translator.trans('oauth.captcha_code_error_tip');
        }
        if (callback) {
          callback(isSuccess);
        }
      }).error(res => {
        console.log(res);
      });
      return this.optional(element) || isSuccess;
    }, Translator.trans('validate.captcha_checkout.message'));
  }

  initCaptchaCode() {
    const $getCodeNum = $('#getcode_num');
    if (!$getCodeNum.length) {
      return;
    }
    Api.captcha.get({ async: false, promise: false }).done(res => {
    $getCodeNum.attr('src', res.image);
      this.captchaToken = res.captchaToken;
    }).error(res => {
      console.log('catch', res.responseJSON.error.message);
    });
    return this.captchaToken;
  }

  sendMessage() {
    const $smsCode = $('.js-sms-send');
    const $captchaCode = $('#captcha_code');
    if (!$smsCode.length) {
      return;
    }
    $smsCode.click((event) => {
      const $target = $(event.target);
      let data = {
        type: 'register',
        mobile: $('.js-account').html(),
        captchaToken: this.captchaToken,
        phrase: $captchaCode.val()
      };
      Api.sms.send({ data: data }).then((res) => {
        this.smsToken = res.smsToken;
        countDown(120);
      }).catch((res) => {
        const code = res.responseJSON.error.code;
        switch(code) {
          case 30001:
            notify('danger', Translator.trans('oauth.refresh.captcha_code_tip'));
            $captchaCode.val('');
            $target.attr('disabled', true);
            this.initCaptchaCode();
            break;
          case 30002:
            notify('danger', Translator.trans('oauth.send.error_message_tip'));
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
  }

  changeCaptchaCode() {
    const $getCodeNum = $('#getcode_num');
    if (!$getCodeNum.length) {
      return;
    }
    $getCodeNum.click(() => {
      this.initCaptchaCode();
    });
  }

  submitForm() {
    this.$btn.click((event) => {
      const $target = $(event.target);
      if (!this.validator.form()) {
        return;
      }
      $target.button('loading');
      let data = {
        nickname: $('#username').val(),
        password: $('#password').val(),
        mobile: $('.js-account').html(),
        smsToken: this.smsToken,
        smsCode: $('#sms-code').val(),
        captchaToken: this.captchaToken,
        phrase: $('#captcha_code').val()
      }
      const errorTip = Translator.trans('oauth.send.sms_code_error_tip');
      $.post($target.data('url'), data, (response) => {
        if (response.success === 1) {
          window.location.href = response.url;
        } else {
          $target.button('reset');
          if (!$('.js-password-error').length) {
            $target.prev().addClass('has-error').append(`<p id="password-error" class="form-error-message js-password-error">${errorTip}</p>`);
          }
        }
      }).error((response) => {
        if (response.status === 429) {
          notify('danger', Translator.trans('oauth.register.time_limit'));
        } else {
          notify('danger', Translator.trans('oauth.register.error_message'));
        }
      })
    })

    enterSubmit(this.$form, this.$btn);
  }

  removeSmsErrorTip() {
    $('#sms-code').focus(() => {
      const $tip = $('.js-password-error');
      if ($tip.length) {
        $tip.remove();
      }
    })
  }

}