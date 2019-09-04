import SmsSender from 'app/common/widget/sms-sender';
import Cookies from 'js-cookie';
import notify from 'common/notify';
import Drag from 'app/common/drag';

export default class MobileBind {
  constructor() {
    this.$form = $('#mobile-bind-form');
    this.$smsCode = this.$form.find('.js-sms-send');
    this.drag = null;
    this.initCheckCookie();
    this.dragEvent();
    this.initValidator();
    this.initMobileCodeSendBtn();
  }

  dragEvent() {
    let self = this;
    if (this.drag) {
      this.drag.on('success', function(token){
        self.$smsCode.removeClass('disabled').attr('disabled', false);
      });
    }
  }

  initCheckCookie() {
    let key = this.$form.data('userId') + '-last-login-in';

    if (!Cookies.get(key) || Cookies.get(key) != new Date().getDate()) {
      this.drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
        limitType: 'web_register'
      }) : null
      $('#mobile-bind-modal').modal('show');
      Cookies.set(key, new Date().getDate());
    }
  }

  initValidator() {
    let self = this;

    this.validator = this.$form.validate({
      currentDom: '#submit-btn',
      ajax: true,
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
                self.$smsCode.removeAttr('disabled');
              } else {
                self.$smsCode.attr('disabled', true);
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
          required: Translator.trans('site.captcha_code.required')
        }
      },
      submitSuccess(data) {
        notify('success', Translator.trans(data.message));
        $('.modal').modal('hide');
      },
      submitError(data) {
        notify('danger',  Translator.trans(data.responseJSON.message));
      }
    });
  }

  initMobileCodeSendBtn() {
    let self = this;

    this.$smsCode.on('click', function () {
      self.$smsCode.attr('disabled', true);
      new SmsSender({
        element: '.js-sms-send',
        url: self.$smsCode.data('url'),
        smsType: 'sms_bind',
        captcha: true,
        captchaValidated: true,
        captchaNum: 'dragCaptchaToken',
        preSmsSend: function() {
          return true;
        },
        error: function(error) {
          self.drag.initDragCaptcha();
        },
        additionalAction: function(ackResponse) {
          if (ackResponse == 'captchaRequired') {
            self.$smsCode.attr('disabled', true);
            $('.js-drag-jigsaw').removeClass('hidden');
            if(self.drag) {
              self.drag.initDragCaptcha();
            }
            return true;
          }
          return false;
        }
      });
    });
  }

}