import SmsSender from 'app/common/widget/sms-sender';

export default class CaptchaModal {
  constructor($element, dataTo, smsType, captchaNum) {
    this.$element = $element;
    this.dataTo = dataTo;
    this.smsType = smsType;
    this.captchaNum = captchaNum;
    this.CaptchaValidator = null;
    this.init();
  }
  init() {
    this.$element.on('click', '#getcode_num', (event) => this.changeCaptcha(event));
    $('.js-captcha-btn').click((event) => this.submitForm(event));
    this.initValidator();
  }
  changeCaptcha(e) {
    var $code = $(e.currentTarget);
    $code.attr('src', $code.data('url') + '?' + Math.random());
  }

  submitForm() {
    if(this.CaptchaValidator.form()) {
      this.$element.submit();
    }
  }

  initValidator() {
    this._captchaValidated = false;
    this.CaptchaValidator = this.$element.validate({
      onkeyup: false,
      onfocusout: false,
      rules: {
        captcha_num: {
          required: true,
          alphanumeric: true,
        }
      },
      messages: {
        captcha_num: {
          required: Translator.trans('auth.mobile_captcha_required_error_hint'),
        }
      },
      submitHandler:  (form) =>{
        console.log('submitHandler');
        $.get(this.$element.attr('action'), { value: $('#captcha_num_modal').val() }, (response) => {
          if (response.success) {
            this.$element.parents('.modal').modal('hide');
            this._captchaValidated = true;
            var smsSender = new SmsSender({
              element: '.js-sms-send',
              url: $('.js-sms-send').data('smsUrl'),
              smsType: this.smsType,
              dataTo: this.dataTo,
              captchaNum: this.captchaNum,
              captcha: true,
              captchaValidated: this._captchaValidated,
              preSmsSend: function () {
                var couldSender = true;
                return couldSender;
              }
            });
            $('.js-sms-send').off('click');
          } else {
            this._captchaValidated = false;
            this.$element.find('#getcode_num').attr('src', $('#getcode_num').data('url') + '?' + Math.random());
            this.$element.find('.help-block').html('<span class="color-danger">' + Translator.trans('auth.mobile_captcha_error_hint') + '</span>');
            this.$element.find('.help-block').show();
          }
        }, 'json');
      }
    });
    $('#captcha_num_modal').keydown((event) => {
      if (event.keyCode == 13) {
        this.CaptchaValidator.form();
      }
    });
  }
}
