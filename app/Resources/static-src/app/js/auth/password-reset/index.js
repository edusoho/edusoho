import SmsSender from 'app/common/widget/sms-sender';
import Drag from 'app/common/drag';
import Api from 'common/api';
import notify from 'common/notify';

class Reset {
  constructor() {
    this.event();
    this.dragHtml = $('.js-drag-container').html();
    $('.js-drag-container').remove();
    $('#password-reset-form').prepend(this.dragHtml);
    this.drag = new Drag($('#drag-btn'), $('.js-jigsaw'));
    this.smsEvnet();
    this.validator();
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
        self.drag.unbindEvent();
        delete self.drag;
        $('.js-drag').remove();
        $('form').hide();
        $target.show();
        $target.prepend(self.dragHtml);
        self.drag = new Drag($('#drag-btn'), $('.js-jigsaw'));
      }
    });
  }

  smsEvnet() {
    let $smsCode = $('.js-sms-send');
    $('.js-sms-send').click(() => {
      const smsSender = new SmsSender({
        element: '.js-sms-send',
        url: $smsCode.data('smsUrl'),
        smsType: $smsCode.data('smsType'),
        preSmsSend: () => {
          return true;
        }
      });
    });
  }

  validator() {
    $('#password-reset-form').validate({
      rules: {
        email: {
          required: true,
          email: true,
        },
        dragCaptchaToken: {
          required: true,
        }
      },
      messages: {
        dragCaptchaToken: {
          required: Translator.trans('site.captcha_code.required'),
        }
      },
      submitHandler: function(form) {
        let email = $('#password-reset-form').find('[name="email"]').val();
        let token = $('#password-reset-form').find('[name="dragCaptchaToken"]').val();
        
        Api.resetPasswordEmail.patch({
          token: token,
          email: email,
        }).then((res) => {
          notify('success', '重置密码邮件已发送');
          window.location.href = $('#password-reset-form').data('success') + '?email='+ email;
        });
      }
    });

    $('#password-reset-by-mobile-form').validate({
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
          es_remote: {
            type: 'get'
          },
        },
        dragCaptchaToken: {
          required: true,
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
      }
    });
  }
}

new Reset();
