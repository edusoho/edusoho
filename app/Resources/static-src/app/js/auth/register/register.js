import SmsSender from 'app/common/widget/sms-sender';
import Drag from 'app/common/drag';

export default class Register {
  constructor() {
    this.initValidator();
    this.inEventMobile();
    this.initDragCaptchaCodeRule();
    this.initInviteCodeRule();
    this.initUserTermsRule();
    this.initMobileMsgVeriCodeSendBtn();

    this.drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw')) : null;
  }

  initValidator() {
    $('#register-form').validate({
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
        emailOrMobile: {
          required: true,
          email_or_mobile_check: true,
          es_remote: {
            type: 'get',
            callback: function(bool) {
              if (bool) {
                $('.js-sms-send-btn').removeClass('disabled');
              } else {
                $('.js-sms-send-btn').addClass('disabled');
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
                $('.js-sms-send-btn').removeClass('disabled');
              } else {
                $('.js-sms-send-btn').addClass('disabled');
              }
            }
          }
        }
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
        }
      },
    });

    $.validator.addMethod('email_or_mobile_check', function(value, element, params) {
      let reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      var reg_mobile = /^1\d{10}$/;
      var result = false;
      var isEmail = reg_email.test(value);
      var isMobile = reg_mobile.test(value);
      if (isMobile) {
        $('.email_mobile_msg').removeClass('hidden');
        $('.js-drag-jigsaw').addClass('hidden');
      } else {
        $('.email_mobile_msg').addClass('hidden');
        $('.js-drag-jigsaw').removeClass('hidden');
      }
      if (isEmail || isMobile) {
        result = true;
      }
      $.validator.messages.email_or_mobile_check = Translator.trans('请输入正确的手机／邮箱');
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
  

  initInviteCodeRule() {
    let $invitecode = $('.invitecode');
    if ($invitecode.length > 0) {
      $invitecode.rules('add', {
        required: false,
        reg_inviteCode: true,
        es_remote: {
          type: 'get'
        }
      });
    }
  }

  initUserTermsRule() {
    if ($('#user_terms').length) {
      $('#user_terms').rules('add', {
        required: true,
        messages: {
          required: Translator.trans('validate.user_terms.message')
        }
      });
    }
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
    $smsSendBtn.click(function() {
      let fieldName = $('[name=\'verifiedMobile\']').length ? 'verifiedMobile' : 'emailOrMobile';
      new SmsSender({
        element: '.js-sms-send',
        url: $(this).data('smsUrl'),
        smsType: 'sms_registration',
        dataTo: fieldName,
        captcha: false,
        preSmsSend: function() {
          return true;
        },
        additionalAction: function(ackResponse) {
          console.log(ackResponse);
          if (ackResponse == 'captchaRequired') {
            $smsSendBtn.attr('disabled', true);
            $('.js-drag-jigsaw').removeClass('hidden');
            return true;
          }
          return false;
        }
      });
    });
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
}