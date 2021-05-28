import Drag from 'app/common/drag';

let $form = $('#login-form');
let drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
  limitType: 'user_login'
}) : null;
let validator = $form.validate({
  rules: {
    mobile: {
      required: true,
      phone: true,
    },
    sms_code: {
      required: true,
      unsigned_integer: true,
      rangelength: [6, 6],
      passwordSms: true,
    },
    dragCaptchaToken: {
      required: true,
    },
  },
  messages: {
    dragCaptchaToken: {
      required: Translator.trans('auth.register.drag_captcha_tips')
    },
    sms_code: {
      required: Translator.trans('auth.password_reset.sms_code_required_hint'),
      rangelength: Translator.trans('auth.password_reset.sms_code_validate_hint'),
    },
  }
});

$('.js-btn-login').click((event) => {
  if (validator.form()) {
    $(event.currentTarget).button('loadding');
    $form.submit();
  }
});