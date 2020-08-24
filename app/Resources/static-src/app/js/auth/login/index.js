import Face from './face';
import Drag from 'app/common/drag';

let $form = $('#login-form');
let drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
  limitType: 'user_login'
}) : null;
let validator = $form.validate({
  rules: {
    _username: {
      required: true,
    },
    _password: {
      required: true,
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
$('#login-form').keypress(function (e) {
  if (e.which == 13) {
    $('.js-btn-login').trigger('click');
    e.preventDefault(); // Stops enter from creating a new line
  }
});

$('.js-btn-login').click((event) => {
  if (validator.form()) {
    $(event.currentTarget).button('loadding');
    $form.submit();
  }
});

$('.receive-modal').click();


if ($('.js-sts-login-link').length) {
  new Face({
    element: $('.js-login-main'),
  });
}