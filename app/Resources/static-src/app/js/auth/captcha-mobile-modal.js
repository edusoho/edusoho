import SmsSender from 'app/common/widget/sms-sender';

export default class CaptchaModal {
  constructor($element, dataTo, smsType, captchaNum) {
    this.$element = $element;
    this.dataTo = dataTo;
    this.smsType = smsType;
    this.captchaNum = captchaNum;
    this.validator = null;
    this.init();
  }
  init() {
    this.$element.on('click', '#getcode_num', (event) => this.changeCaptcha(event));
    $('.js-captcha-btn').click((event) => this.submitForm(event));
    this.initValidator();
  }
  changeCaptcha(e) {
    console.log(e);
    var $code = $(e.currentTarget);
    $code.attr("src", $code.data("url") + "?" + Math.random());
  }

  submitForm() {
    console.log(this.validator);
    if (this.validator.form()) {
      $.get(this.$element.attr('action'), { value: $('#captcha_num_modal').val() }, (response) => {
        console.log(response);
        if (response.success) {
          this.$element.parents('.modal').modal('hide');
          this._captchaValidated = true;
          var smsSender = new SmsSender({
            element: '.js-sms-send',
            url: $('.js-sms-send').data('smsUrl'),
            smsType: self.get('smsType'),
            dataTo: self.get('dataTo'),
            captchaNum: self.get('captchaNum'),
            captcha: true,
            captchaValidated: this._captchaValidated,
            preSmsSend: function () {
              var couldSender = true;

              return couldSender;
            }
          });
          smsSender.undelegateEvents('.js-sms-send', 'click');

        } else {
          this._captchaValidated = false;
          this.$element.find('#getcode_num').attr("src", $("#getcode_num").data("url") + "?" + Math.random());
          this.$element.find('.help-block').html('<span class="text-danger">' + Translator.trans('验证码错误') + '</span>');
          this.$element.find('.help-block').show();
        }
      }, 'json');
    }
  }

  initValidator() {
    this._captchaValidated = false;
    this.validator = this.$element.validate({
      rules: {
        captcha_num: {
          required: true,
          alphanumeric: true,
        }
      }
    });
  }
}