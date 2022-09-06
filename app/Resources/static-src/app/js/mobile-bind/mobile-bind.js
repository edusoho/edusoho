import SmsSender from 'app/common/widget/sms-sender';
import Cookies from 'js-cookie';
import notify from 'common/notify';
import Drag from 'app/common/drag';
import Coordinate from 'app/common/coordinate';

export default class MobileBind {
  constructor() {
    this.$form = $('#mobile-bind-form');
    this.$smsCode = this.$form.find('.js-sms-send');
    this.drag = null;
    this.initDrag();
    this.dragEvent();
    this.initValidator();
    this.initMobileCodeSendBtn();
    this.bindMobile();
    this.initCheckCookie();
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
    $('.js-skip-bind').click(function (){
      let key = 'is_skip_mobile_bind';
      if (!Cookies.get(key) || Cookies.get(key) == 0) {
        Cookies.set(key, 1);
      }
      window.location.href = $('#submit-btn').data('targetUrl');
    })
  }

  initDrag() {
    this.drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
      limitType: 'web_register'
    }) : null
  }

  initValidator() {
    let self = this;

    this.validator = this.$form.validate({
      currentDom: '#submit-btn',
      ajax: true,
      rules: {
        mobile: {
          required: true,
          phone: true,
          es_remote: {
            type: 'get',
            callback: (bool) => {
              if (bool) {
                self.$smsCode.removeAttr('disabled');
                $('.binded-tip').addClass('hidden');
              } else {
                self.$smsCode.attr('disabled', true);
                $('.binded-tip').removeClass('hidden');
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
          required: Translator.trans('auth.mobile_captcha_required_error_hint')
        }
      },
      submitSuccess(data) {
        notify('success', Translator.trans(data.message));
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
      let coordinate = new Coordinate();
      const encryptedPoint = coordinate.getCoordinate(event, $('meta[name=csrf-token]').attr('content'));
      new SmsSender({
        element: '.js-sms-send',
        url: self.$smsCode.data('url'),
        smsType: 'sms_bind',
        captcha: true,
        captchaValidated: true,
        captchaNum: 'dragCaptchaToken',
        encryptedPoint: encryptedPoint,
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

  bindMobile() {
    let self =  this;
    $('#submit-btn').click(function(){
      if (self.validator.form()){
        $.post(self.$form.data('url'), self.$form.serialize(), function(response) {
          notify('success', Translator.trans(response.message));
          window.location.href = $('#submit-btn').data('targetUrl');
        }).error(function(response){
          notify('danger',  Translator.trans(response.responseJSON.message));
        });
      }
    })
  }
}