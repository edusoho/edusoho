import SmsSender from 'app/common/widget/sms-sender';
import Drag from 'app/common/drag';
import Coordinate from 'app/common/coordinate';
import Api from 'common/api';

export default class Register {
  constructor() {
    this.drag = $('#drag-btn').length
      ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
          limitType: 'web_register'
        })
      : null;
    this.setValidateRule();
    this.dragEvent();
    this.initPasswordEyeClickEvent();
    this.initValidator();
    this.initCodeValidateEvent();
    this.initEmailOrMobileEvent();
    this.initCodeSendBtn();
    this.initFieldVisitId();
    this.submitFrom();
    this.initRegisterModeSwitch()
    this.initEmailMobileMsg();
  }

  dragEvent() {
    let self = this;
    if (this.drag) {
      this.drag.on('success', function(token) {
        self._codeBtnEnable();
      });
    }
  }

  initPasswordEyeClickEvent() {
    $('.open-eye').on('click', function () {
      $('#register_password').attr('type', 'password');
      $('.open-eye').hide();
      $('.close-eye').show();
    });

    $('.close-eye').on('click', function () {
      $('#register_password').attr('type', 'text');
      $('.close-eye').hide();
      $('.open-eye').show();
    });
  }

  resetDragCaptchaAndCodeBtn() {
    this._codeBtnDisable();
    this.drag.initDragCaptcha();
    $('input[name="dragCaptchaToken"]').val('')
  }

  initRegisterModeSwitch() {
    if ($('#register_mode_switch').length === 0) return;

    $('#register_mode_switch').text('切换邮箱号注册 >>').attr('mode', 'mobile')
    $('#register_emailOrMobile-label').text('手机号码');
    $('#register_emailOrMobile-input').attr('placeholder', '请填写你常用的手机号码作为登陆账号');
    $('#register_emailOrMobile-label').attr('for', 'verifiedMobile')
    $('#register_emailOrMobile-input').attr('name', 'verifiedMobile')
    this._codeBtnDisable();

    $('#register_mode_switch').on('click', () => {
      this.resetValidation();
      this.resetDragCaptchaAndCodeBtn()
      $('#register_emailOrMobile-input').val('');
      if ($('#register_mode_switch').attr('mode') === 'email') {
        $('#register_mode_switch').text('切换邮箱号注册 >>').attr('mode', 'mobile')
        $('#register_emailOrMobile-label').text('手机号码');
        $('#register_emailOrMobile-input').attr('placeholder', '请填写你常用的手机号码作为登陆账号');
        $('#register_emailOrMobile-label').attr('for', 'verifiedMobile')
        $('#register_emailOrMobile-input').attr('name', 'verifiedMobile')
        $('#register_emailOrMobile-input').attr('type', 'tel')
      } else if ($('#register_mode_switch').attr('mode') === 'mobile') {
        $('#register_mode_switch').text('切换手机号注册 >>').attr('mode', 'email')
        $('#register_emailOrMobile-label').text('邮箱地址');
        $('#register_emailOrMobile-input').attr('placeholder', '请填写你常用的邮箱地址作为登陆账号');
        $('#register_emailOrMobile-label').attr('for', 'email')
        $('#register_emailOrMobile-input').attr('name', 'email')
        $('#register_emailOrMobile-input').attr('type', 'email')
      }
      this.initEmailMobileMsg();
    })
  }

  resetValidation() {
    const $form = $('#register-form');
    const validator = $form.validate();
    validator.resetForm();
    $('.error').removeClass('error');
    $('.error-message').remove();
    this._codeBtnDisable();
  }

  initEmailMobileMsg() {
    const register_mode = $('input[name="register_mode"]').val();

    if (register_mode === 'email') {
      $('.js-email_mobile_msg-input').attr('placeholder', '填写邮箱验证码')
      $('.js-email_mobile_msg-input').attr('name', 'email_code')
      $('.js-email_mobile_msg-input').attr('id', 'email_code')

      $('.js-email_mobile_msg-label').text('邮箱验证码')
      $('.js-email_mobile_msg-label').attr('for', 'email_code')
    } else if (register_mode === 'mobile') {
      $('.js-email_mobile_msg-input').attr('placeholder', '填写短信验证码')
      $('.js-email_mobile_msg-input').attr('name', 'sms_code')
      $('.js-email_mobile_msg-input').attr('id', 'sms_code')

      $('.js-email_mobile_msg-label').text('短信验证码')
      $('.js-email_mobile_msg-label').attr('for', 'sms_code')
    } else {
      if ($('#register_mode_switch').attr('mode') === 'email') {
        $('.js-email_mobile_msg-input').attr('placeholder', '填写邮箱验证码')
        $('.js-email_mobile_msg-input').attr('name', 'email_code')
        $('.js-email_mobile_msg-input').attr('id', 'email_code')

        $('.js-email_mobile_msg-label').text('邮箱验证码')
        $('.js-email_mobile_msg-label').attr('for', 'email_code')
      } else if ($('#register_mode_switch').attr('mode') === 'mobile') {
        $('.js-email_mobile_msg-input').attr('placeholder', '填写短信验证码')
        $('.js-email_mobile_msg-input').attr('name', 'sms_code')
        $('.js-email_mobile_msg-input').attr('id', 'sms_code')

        $('.js-email_mobile_msg-label').text('短信验证码')
        $('.js-email_mobile_msg-label').attr('for', 'sms_code')

      }
    }
  }

  setValidateRule() {
    $.validator.addMethod(
      'spaceNoSupport',
      function(value, element) {
        return value.indexOf(' ') < 0;
      },
      $.validator.format(Translator.trans('validate.have_spaces'))
    );
  }

  initValidator() {
    $('#register-form').validate({
      ...this._validateRules(),
    });
  }

  initCodeValidateEvent() {
    $('#register_mobile').blur(() => {
      let mobile = $('#register_mobile').val();
      this.smsCodeValidate(mobile);
    });
    $('#register_email').blur(() => {
      let email = $('#register_email').val();
      this.emailCodeValidate(email);
    });
  }

  initEmailOrMobileEvent() {
    $('#register_emailOrMobile-input').blur(() => {
      $('input[name="emailOrMobile"]').val($('#register_emailOrMobile-input').val());
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

  _codeBtnDisable() {
    $('.js-code-send-btn')
      .addClass('disabled')
      .attr('disabled', true);
  }

  _codeBtnEnable() {
    $('.js-code-send-btn')
      .removeClass('disabled')
      .attr('disabled', false);
  }

  initSmsCodeRule() {
    $('[name="sms_code"]').rules('add', {
      required: true,
      unsigned_integer: true,
      rangelength: [6, 6],
      messages: {
        rangelength: Translator.trans('validate.sms_code.message')
      }
    });
  }

  initEmailCodeRule() {
    $('[name="email_code"]').rules('add', {
      required: true,
      unsigned_integer: true,
      rangelength: [6, 6],
      messages: {
        rangelength: Translator.trans('validate.sms_code.message')
      }
    });
  }

  startCountdown($button, seconds) {
    let remaining = seconds;
    let originalText = $button.text();
    this._codeBtnDisable();
    $button.text(remaining + '秒后重新获取');
    let countdown = setInterval(() => {
      remaining--;
      if (remaining <= 0) {
        clearInterval(countdown);
        this._codeBtnEnable();
        $button.text(originalText);
      } else {
        $button.text(remaining + '秒后重新获取');
      }
    }, 1000);
  }

  initCodeSendBtn() {
    let $codeSendBtn = $('.js-code-send-btn');
    let self = this;
    const register_mode = $('input[name="register_mode"]').val();
    $codeSendBtn.click(function(event) {
      if(!$('input[name="dragCaptchaToken"]').val() || (!$('input[name="verifiedMobile"]').val() && !$('input[name="email"]').val())) return;
      self._codeBtnDisable();
      if (register_mode === 'mobile' || $('#register_mode_switch').length > 0 && $('#register_mode_switch').attr('mode') === 'mobile') {
        let coordinate = new Coordinate();
        const encryptedPoint = coordinate.getCoordinate(
          event,
          $('meta[name=csrf-token]').attr('content')
        );
        let fieldName = $('[name="verifiedMobile"]').length
          ? 'verifiedMobile'
          : 'emailOrMobile';
        new SmsSender({
          element: $codeSendBtn,
          url: $(this).data('smsUrl'),
          smsType: 'sms_registration',
          dataTo: fieldName,
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
            if (ackResponse === 'captchaRequired') {
              $codeSendBtn.attr('disabled', true);
              $('.js-drag-jigsaw').removeClass('hidden');
              self.captchEnable = true;
              if (self.drag) {
                self.drag.initDragCaptcha();
              }
              return true;
            }
            return false;
          }
        });
      } else if (register_mode === 'email' || $('#register_mode_switch').length > 0 && $('#register_mode_switch').attr('mode') === 'email') {
        let params = {
          email: register_mode === 'email' ? $('#register_email').val() : $('#register_emailOrMobile-input').val(),
          dragCaptchaToken: $('[name="dragCaptchaToken"]').val()
        }
        Api.user.sendEmailCode({
          data: params
        }).then((res) => {
          $('[name="emailToken"]').val(res.emailToken);
          self.startCountdown($codeSendBtn, 120);
        });
      }
    });
  }

  _validateRules() {
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
            type: 'get'
          }
        },
        password: {
          spaceNoSupport: true,
          password_normal: true,
        },
        invitedCode: {
          required: false,
          reg_inviteCode: true,
          es_remote: {
            type: 'get'
          }
        },
        email: {
          required: true,
          email: true,
          es_remote: {
            type: 'get',
            callback: function(bool) {
              if (bool) {
                self._codeBtnEnable();
              } else {
                self._codeBtnDisable();
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
                self._codeBtnEnable();
              } else {
                self._codeBtnDisable();
              }
            }
          }
        },
        dragCaptchaToken: {
          required: true
        }
      },
      messages: {
        nickname: {
          required: Translator.trans(
            'auth.register.nickname_required_error_hit'
          )
        },
        verifiedMobile: {
          required: Translator.trans('validate.phone.message')
        },
        email: {
          required: Translator.trans('validate.valid_email_input.message'),
        },
        dragCaptchaToken: {
          required: Translator.trans('auth.register.drag_captcha_tips')
        },
        password: {
          required: Translator.trans('password.hint.normal'),
        }
      },
    };
  }

  smsCodeValidate(mobile) {
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

  emailCodeValidate(email) {
    let reg_email = /^([a-zA-Z0-9_.\-+])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    let isEmail = reg_email.test(email);
    if (isEmail) {
      this.initEmailCodeRule();
      $('[name="dragCaptchaToken"]').rules('remove');
    } else {
      this.initDragCaptchaCodeRule();
      $('[name="email_code"]').rules('remove');
    }
  }

  initFieldVisitId() {
    $(document).ready(() => {
      if ('undefined' !== window._VISITOR_ID) {
        $('[name="registerVisitId"]').val(window._VISITOR_ID);
      }
    });
  }

  submitFrom() {
    const $registerFrom = $('#register-form');
    const $modal = $('#modal');

    $('#register-btn').on('click', () => {
      const validator = $registerFrom.validate();
      const inputCheckbox = $('input[name="agree_policy"]').prop('checked');

      if (!validator.form()) return;

      if (inputCheckbox || inputCheckbox == undefined) {
        $registerFrom.submit();

        return;
      }

      // $('#modal').modal({backdrop:'static'}); // 点击遮罩关闭弹框
      $modal.load('/register/agreement');
      $modal.modal('show');

      $modal.on('click', '.js-agree-register', () => {
        $('input[name="agree_policy"]').prop('checked', true);
        $modal.modal('hide');
        $registerFrom.submit();
      });

      $modal.on('click', '.js-close-modal', () => {
        $modal.modal('hide');
      });
    });
  }
}
