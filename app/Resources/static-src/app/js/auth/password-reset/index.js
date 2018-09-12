import SmsSender from 'app/common/widget/sms-sender';
import Drag from 'app/common/drag';
import Api from 'common/api';
import notify from 'common/notify';
import { countDown } from 'app/common/new-count-down.js';
require('app/common/xxtea.js');

class Reset {
  constructor() {
    this.event();
    this.dragHtml = $('.js-drag-box').html();
    $('.js-drag-box').remove();
    $('#password-reset-form').prepend(this.dragHtml);
    this.drag = new Drag($('#drag-btn'), $('.js-jigsaw'), {
      limitType: 'reset_password',
    });
    this.smsEvent();
    this.validator();
    this.smsToken = '';
  }

  event() {
    let self = this;
    $('.js-find-password li').click(function(){
      let $this = $(this);
      if ($this.hasClass('active')) {
        return;
      }
      $this.addClass('active').siblings().removeClass('active');

      let $target = $($this.data('target'));
      if ($target.length > 0 ) {
        $('form').hide();
        self.drag.unbindEvent();
        $('.js-drag').remove();
        $target.prepend(self.dragHtml);

        self.drag = new Drag($('#drag-btn'), $('.js-jigsaw'), {limitType: 'reset_password'});
        $target.show();
      }
    });
  }

  smsEvent() {
    let $smsCode = $('.js-sms-send');
    let self = this;
    $smsCode.click(() => {
      if(this.mobileValidator.element($('[name="dragCaptchaToken"]'))) {
        if($smsCode.hasClass('disabled')) {
          return ;
        }
        $smsCode.addClass('disabled');

        Api.resetPasswordSms.get({
          params: {
            mobile: $('#mobile').val(),
          },
          data: {
            dragCaptchaToken: $('[name="dragCaptchaToken"]').val()
          }
        }).then((res) => {
          notify('success', Translator.trans('notify.sms_send_success.message'));
          $smsCode.removeClass('disabled');
          countDown($('.js-sms-send'), $('#js-fetch-btn-text'), 120);
          self.smsToken = res.smsToken;
        }).catch(()=> {
          $smsCode.removeClass('disabled');
          self.drag.initDragCaptcha();
        });
      }
    });
  }

  validator() {
    let self = this;
    $('#password-reset-form').validate({
      rules: {
        email: {
          required: true,
          email: true,
        },
        dragCaptchaToken: {
          required: true,
        },
      },
      messages: {
        dragCaptchaToken: {
          required: Translator.trans('site.captcha_code.required'),
        },
      },
      submitHandler: function(form) {
        let email = $('#password-reset-form').find('[name="email"]').val();
        Api.resetPasswordEmail.patch({
          params: {
            email: email,
          },
          data: {
            dragCaptchaToken: $('[name="dragCaptchaToken"]').val(),
          }
        }).then((res) => {
          notify('success', Translator.trans('notify.password_reset_email_send_success.message'));
          window.location.href = $('#password-reset-form').data('success') + '?email='+ email;
        }).catch((res) => {
          let code = res.responseJSON.error.code||0;
          if (4040104 == code) {
            self.drag.initDragCaptcha();
          }
        });
      }
    });

    $.validator.addMethod('passwordSms', function (value, element) {
      let result = false;
      Api.resetPasswordSms.validate({
        params: {
          mobile: $('#mobile').val(),
          smsCode: $('#sms-code').val()
        },
        data: {
          smsToken: self.smsToken,  
        },
        async: false,
        promise: false,
      }).success((res) => { 
        result = 'sms.code.success' == res ? true : false;
      });

      return result;
    }, $.validator.format(Translator.trans('validate.sms_code.message')));


    this.mobileValidator = $('#password-reset-by-mobile-form').validate({
      rules: {
        'mobile': {
          required: true,
          phone: true,
          es_remote: {
            type: 'get',
            callback: (bool) => {
              if (bool) {
                $('.js-sms-send').removeClass('disabled');
              } else {
                $('.js-sms-send').addClass('disabled');
              }
            }
          }
        },
        'sms_code': {
          required: true,
          unsigned_integer: true,
          rangelength: [6, 6],
          passwordSms: true,
        },
        dragCaptchaToken: {
          required: true,
        },
        'reset_password': {
          required: true,
          minlength: 5,
          maxlength: 20,
        }
      },
      messages: {
        sms_code: {
          required: Translator.trans('auth.password_reset.sms_code_required_hint'),
          rangelength: Translator.trans('auth.password_reset.sms_code_validate_hint'),
        },
        dragCaptchaToken: {
          required: Translator.trans('site.captcha_code.required'),
        }
      },
      submitHandler: function(form) {
        Api.resetPasswordMobile.patch({
          params: {
            mobile: $('#mobile').val(),
          },
          data: {
            smsToken: self.smsToken,
            smsCode: $('#sms-code').val(),
            encrypt_password: window.XXTEA.encryptToBase64($('#reset_password').val(), window.location.host),
            dragCaptchaToken: $('[name="dragCaptchaToken"]').val(),
          }
        }).then((res) => {
          notify('success', Translator.trans('notify.password_reset_success.message'));
          window.location.href = $('#password-reset-form').data('success') + '?mobile='+ $('#mobile').val();
        });
      }
    });
  }
}

new Reset();
