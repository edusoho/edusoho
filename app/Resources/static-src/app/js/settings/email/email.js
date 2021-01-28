import Drag from 'app/common/drag';
import notify from 'common/notify';

export default class Email {
  constructor() {
    this.drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
      limitType: 'web_register'
    }) : null;
    this.dragEvent();
    this.initValidator();
    this.initDragCaptchaCodeRule();
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
    let $btn = $('#submit-btn');
    let self = this;

    $('#setting-email-form').validate({
      currentDom: '#submit-btn',
      ajax: true,
      rules: {
        'password': 'required',
        'email': 'required es_email',
        'dragCaptchaToken': 'required'
      },
      messages: {
        dragCaptchaToken: {
          required: Translator.trans('auth.register.drag_captcha_tips')
        },
      },
      submitSuccess(data) {
        $('#modal').html(data);
      },
      submitError(data) {
        notify('danger',  Translator.trans(data.responseJSON.message));
        if (self.drag) {
          self.drag.initDragCaptcha();
        }
      }
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

  _smsBtnable() {
    $('.js-sms-send-btn').removeClass('disabled').attr('disabled', false);
  }
}