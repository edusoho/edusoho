import SmsSender from 'app/common/widget/sms-sender';

export default class Register {
  constructor() {
    this.initDate();
    this.initValidator();
    this.inEventMobile();
    this.initCaptchaCode();
    this.initDragCaptchaCodeRule();
    this.initRegisterTypeRule();
    this.initInviteCodeRule();
    this.initUserTermsRule();
    this.initMobileMsgVeriCodeSendBtn();
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
        $('.js-captcha, .js-drag-jigsaw').addClass('hidden');
      } else {
        $('.email_mobile_msg').addClass('hidden');
        $('.js-captcha, .js-drag-jigsaw').removeClass('hidden');
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

  initDate() {
    $('.date').datetimepicker({
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 'month',
      language: window.document.documentElement.lang
    });
  }

  initCaptchaCode() {
    let $getCodeNum = $('#getcode_num');
    if ($getCodeNum.length > 0) {
      $getCodeNum.click(function() {
        $(this).attr('src', $getCodeNum.data('url') + '?' + Math.random());
      });
      this.initCaptchaCodeRule();
    }
  }

  initRegisterTypeRule() {
    let $email = $('input[name="email"]');
    if ($email.length > 0) {
      $email.rules('add', {
        required: true,
        email: true,
        es_remote: {
          type: 'get'
        },
        messages: {
          required: Translator.trans('validate.valid_email_input.message'),
        }
      });
    }

    let $emailOrMobile = $('input[name="emailOrMobile"]');
    if ($emailOrMobile.length > 0) {
      $emailOrMobile.rules('add', {
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
        },
        messages: {
          required: Translator.trans('validate.phone_and_email_input.message')
        },
      });
    }

    let $verifiedMobile = $('input[name="verifiedMobile"]');
    if ($verifiedMobile.length > 0) {
      $('.email_mobile_msg').removeClass('hidden');
      $verifiedMobile.rules('add', {
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
        },
        messages: {
          required: Translator.trans('validate.phone.message')
        },
      });
    }
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

  initCaptchaCodeRule() {
    $('[name="captcha_code"]').rules('add', {
      required: true,
      alphanumeric: true,
      es_remote: {
        type: 'get',
        callback: function(bool) {
          if (!bool) {
            $('#getcode_num').attr('src', $('#getcode_num').data('url') + '?' + Math.random());
          }
        }
      },
    });
  }

  initDragCaptchaCodeRule() {
    if ($('.js-drag-img').length) {
      $('[name="drag_captcha_token"]').rules('add', {
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
    $('.js-sms-send-btn').click(function() {
      let fieldName = 'emailOrMobile';
      if ($('[name=\'verifiedMobile\']').length > 0) {
        fieldName = 'verifiedMobile';
      }

      new SmsSender({
        element: '.js-sms-send',
        url: $('.js-sms-send').data('smsUrl'),
        smsType: 'sms_registration',
        dataTo: fieldName,
        captcha: false,
        preSmsSend: function() {
          return true;
        },
        additionalAction: function(ackResponse) {
          if (ackResponse == 'captchaRequired') {
            $('.js-sms-send').click();
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
      $('[name="captcha_code"], [name="drag_captcha_token"]').rules('remove');
    } else {
      this.initCaptchaCodeRule();
      this.initDragCaptchaCodeRule();
      $('[name="sms_code"]').rules('remove');
    }
  }

}