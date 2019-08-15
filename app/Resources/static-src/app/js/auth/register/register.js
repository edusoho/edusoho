import SmsSender from 'app/common/widget/sms-sender';
import Drag from 'app/common/drag';

export default class Register {
  constructor() {
    this.drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
      limitType: 'web_register'
    }) : null;
    this.dragEvent();
    this.initValidator();
    this.inEventMobile();
    this.initMobileMsgVeriCodeSendBtn();
    this.initFieldVisitId();
  }

  dragEvent() {
    let self = this;
    if (this.drag) {
      this.drag.on('success', function(token){
        self._smsBtnable();
      });
    }
  }

  initValidator() {
    let self = this;
    $('#register-form').validate(this._validataRules());
    $.validator.addMethod('email_or_mobile_check', function(value, element, params) {
      let reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      var reg_mobile = /^1\d{10}$/;
      var result = false;
      var isEmail = reg_email.test(value);
      var isMobile = reg_mobile.test(value);
      if (isMobile) {
        $('.email_mobile_msg').removeClass('hidden');
        if (!self.captchEnable) {
          $('.js-drag-jigsaw').addClass('hidden');
        }
      } else {
        $('.email_mobile_msg').addClass('hidden');
        $('.js-drag-jigsaw').removeClass('hidden');
      }
      if (isEmail || isMobile) {
        result = true;
      }
      $.validator.messages.email_or_mobile_check = Translator.trans('validate.mobile_or_email_message');
      return this.optional(element) || result;
    }, Translator.trans('validate.email_or_mobile_check.message'));
  }

  inEventMobile() {
    $('#register_emailOrMobile').blur(() => {
      let emailOrMobile = $('#register_emailOrMobile').val();
      this.emSmsCodeValidate(emailOrMobile);
    });

    $('#register_mobile').blur(() => {
      let mobile = $('#register_mobile').val();
      this.emSmsCodeValidate(mobile);
    });
  }

  initDragCaptchaCodeRule() {
    if ($('.js-drag-img').length) {
      $('[name="dragCaptchaToken"]').rules('add', {
        required: true,
        messages: {
          required: Translator.trans('auth.register.drag_captcha_tips')
        }
      });
    }
  }

  _smsBtnDisable() {
    $('.js-sms-send-btn').addClass('disabled').attr('disabled', true);
  }

  _smsBtnable() {
    $('.js-sms-send-btn').removeClass('disabled').attr('disabled', false);
  }

  initSmsCodeRule() {
    $('[name="sms_code"]').rules('add', {
      required: true,
      unsigned_integer: true,
      rangelength: [6, 6],
      es_remote: {
        type: 'get',
      },
      messages: {
        rangelength: Translator.trans('validate.sms_code.message')
      }
    });
  }

  initMobileMsgVeriCodeSendBtn() {
    let $smsSendBtn =  $('.js-sms-send-btn');
    let self = this;
    $smsSendBtn.click(function() {
      self._smsBtnDisable();
      let fieldName = $('[name=\'verifiedMobile\']').length ? 'verifiedMobile' : 'emailOrMobile';
      new SmsSender({
        element: $smsSendBtn,
        url: $(this).data('smsUrl'),
        smsType: 'sms_registration',
        dataTo: fieldName,
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
            $smsSendBtn.attr('disabled', true);
            $('.js-drag-jigsaw').removeClass('hidden');
            self.captchEnable = true;
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

  _validataRules() {
    let self = this;
    return {
      rules: {
        nickname: {
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
          minlength: 5,
          maxlength: 20,
        },
        email: {
          required: true,
          email: true,
          es_remote: {
            type: 'get'
          }
        },
        invitedCode: {
          required: false,
          reg_inviteCode: true,
          es_remote: {
            type: 'get'
          }
        },
        emailOrMobile: {
          required: true,
          email_or_mobile_check: true,
          es_remote: {
            type: 'get',
            callback: function(bool) {
              if (bool) {
                self._smsBtnable();
              } else {
                self._smsBtnDisable();
              }
            }
          }
        },
        verifiedMobile: {
          required: true,
          phone: true,
          es_remote: {
            type: 'get',
            callback: function(bool) {
              if (bool) {
                self._smsBtnable();
              } else {
                self._smsBtnDisable();
              }
            }
          }
        },
        dragCaptchaToken: {
          required: true,
        },
        agree_policy: {
          required: true,
        },
      },
      messages: {
        verifiedMobile: {
          required: Translator.trans('validate.phone.message'),
        },
        emailOrMobile: {
          required: Translator.trans('validate.phone_and_email_input.message'),
        },
        email: {
          required: Translator.trans('validate.valid_email_input.message'),
        },
        dragCaptchaToken: {
          required: Translator.trans('auth.register.drag_captcha_tips')
        },
        agree_policy: {
          required: Translator.trans('validate.valid_policy_input.message'),
        },
      },
    };
  }

  emSmsCodeValidate(mobile) {
    let reg_mobile = /^1\d{10}$/;
    let isMobile = reg_mobile.test(mobile);
    if (isMobile) {
      this.initSmsCodeRule();
      $('[name="dragCaptchaToken"]').rules('remove');
    } else {
      this.initDragCaptchaCodeRule();
      $('[name="sms_code"]').rules('remove');
    }
  }

  initFieldVisitId() {
    $(document).ready(() => {
      if ('undefined' !== window._VISITOR_ID) {
        $('[name="registerVisitId"]').val(window._VISITOR_ID);
      }
    })
  }
}