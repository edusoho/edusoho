import { enterSubmit } from 'app/common/form';
import notify from 'common/notify';
import { countDown } from './count-down';
import Api from 'common/api';
import Drag from 'app/common/drag';

export default class Create {
  constructor() {
    this.$form = $('#third-party-create-account-form');
    this.$btn = $('.js-submit-btn');
    this.validator = null;
    this.dragCaptchaToken = '';
    this.smsToken = null;
    this.$sendBtn = $('.js-sms-send');
    this.drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw')) : false;
    this.init();
  }

  init() {
    this.initValidator();
    this.smsSend();
    this.submitForm();
    this.removeSmsErrorTip();
    this.dragEvent();
  }

  dragEvent() {
    let self = this;
    if (this.drag) {
      $('[name="dragCaptchaToken"]').rules('add', {
        required: true,
        messages: {
          required: Translator.trans('auth.register.drag_captcha_tips')
        }
      });

      this.drag.on('success', function(data){
        self.$sendBtn.attr('disabled', false);
        self.dragCaptchaToken = data.token;
      });
    }
  }

  initValidator() {
    this.rules = {
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
        rangelength: [6, 6],
      },
    };

    this.validator = this.$form.validate({
      rules: this.rules,
      messages: {
        sms_code: {
          required: Translator.trans('site.captcha_code.required'),
          rangelength: Translator.trans('validate.sms_code.message')
        }
      }
    });
  }

  smsSend() {
    let self = this;
    const $captchaCode = $('#captcha_code');
    if (!this.$sendBtn.length) {
      return;
    }
    this.$sendBtn.click((event) => {
      self.$sendBtn.attr('disabled', true);
      let data = {
        type: 'register',
        mobile: $('.js-account').text(),
        dragCaptchaToken: this.dragCaptchaToken,
        phrase: $captchaCode.val()
      };

      Api.sms.send({ data: data }).then((res) => {
        this.smsToken = res.smsToken;
        countDown(120);
      }).catch((res) => {
        if (self.drag) {
          self.drag.initDragCaptcha();
          $('.js-drag-jigsaw').removeClass('hidden');
        }
      });
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
        phrase: $('#captcha_code').val(),
        dragCaptchaToken: $('[name="dragCaptchaToken"]').val(),
      };
      const errorTip = Translator.trans('oauth.send.sms_code_error_tip');
      $.post($target.data('url'), data, (response) => {
        $target.button('reset');
        if (response.success === 1) {
          window.location.href = response.url;
        } else {
          if (!$('.js-password-error').length) {
            $target.prev().addClass('has-error').append(`<p id="password-error" class="form-error-message js-password-error">${errorTip}</p>`);
          }
        }
      }).error((response) => {
        $target.button('reset');
      });
    });

    enterSubmit(this.$form, this.$btn);
  }

  removeSmsErrorTip() {
    $('#sms-code').focus(() => {
      const $tip = $('.js-password-error');
      if ($tip.length) {
        $tip.remove();
      }
    });
  }
}