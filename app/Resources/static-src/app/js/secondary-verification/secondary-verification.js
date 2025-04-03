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
    this.initExportBtnEvent();
  }

  // +++ 新增方法：绑定导出按钮的点击事件 +++
  initExportBtnEvent() {
    const self = this;
    $('.js-export-btn').on('click', function(e) {
      console.log('***********');
      e.preventDefault(); // 防止链接默认行为
      if (self.$form.valid()) { // 手动触发表单验证
        // 如果验证通过，执行后续操作（如提交表单或导出）
        console.log('验证成功，执行导出逻辑...');
      }
    });
  }

  dragEvent() {
    let self = this;
    if (this.drag) {
      this.drag.on('success', function(token){
        self.$smsCode.removeClass('disabled').attr('disabled', false);
      });
    }
  }

  initDrag() {
    this.drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
      limitType: 'web_register'
    }) : null
  }

  initValidator() {
    let self = this;

    this.validator = this.$form.validate({
      currentDom: '.js-export-btn',
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
        smsType: 'sms_secondary_verification',
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
}