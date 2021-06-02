import Face from '../login/face';
import Drag from 'app/common/drag';

let $form = $('#login-ajax-form');
let drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
  limitType: 'user_login'
}) : null;
let $btn = $('.js-submit-login-ajax');
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
    _username: {
      required: Translator.trans('auth.login.username_required_error_hint')
    },
    _password: {
      required: Translator.trans('auth.login.password_required_error_hint')
    },
    dragCaptchaToken: {
      required: Translator.trans('auth.register.drag_captcha_tips')
    },
  }
});


$btn.click((event) => {
  if (validator.form()) {
    $.post($form.attr('action'), $form.serialize(), function (response) {
      $btn.button('loading');
      window.location.reload();
    }, 'json').error(function (jqxhr, textStatus, errorThrown) {
      var json = jQuery.parseJSON(jqxhr.responseText);
      $form.find('.alert-danger').html(Translator.trans(json.message)).show();
      drag.initDragCaptcha();
    });
  }
});


if ($('.js-sts-login-link').length) {
  new Face({
    element: $('.js-login-modal'),
    target: '.js-login-form, .modal-footer',
  });
}

