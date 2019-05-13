import Drag from 'app/common/drag';

export default class emailReset {
  constructor() {
    this.drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
      limitType: 'web_register'
    }) : null;
    this.container = $('#reset-email-form');
    this.btn = $('#next-btn');
    this.initValidator();
    this.init();
  }

  init() {
    let self = this;
    self.btn.click(()=>{
      if(self.validator.form()) {
        $btn.button('loadding');
        self.container.submit();
      }
    });
  }

  initValidator() {
    let self = this;
    this.validator = self.container.validate({
      rules: {
        email: {
          required: true,
          es_email: true,
          es_remote: {
            type: 'get'
          }
        },
        password: {
          required: true,
          minlength: 5,
          maxlength: 20
        },
        dragCaptchaToken: {
          required: true,
        }
      },
      messages: {
        dragCaptchaToken: {
          required: Translator.trans('auth.register.drag_captcha_tips')
        },
      }
    });
  }
}