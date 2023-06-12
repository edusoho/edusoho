import Face from './face';
import Drag from 'app/common/drag';
require('app/common/xxtea.js');

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
    _username: {
      required: Translator.trans('auth.register.name_required_error_hint')
    },
    _password: {
      required: Translator.trans('auth.register.password_required_error_hint')
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
    var username = $form.find('#login_username').val();
    var password = $form.find('#login_password').val();

    const encryptedUsername = window.XXTEA.encryptToBase64(username, 'EduSoho');
    const encryptedPassword = window.XXTEA.encryptToBase64(password, 'EduSoho');

    var formData = $form.serializeArray();

    var fieldsToUpdate = {
      '_username': encryptedUsername,
      '_password': encryptedPassword
    };

    formData.forEach(function(field) {
      if (fieldsToUpdate.hasOwnProperty(field.name)) {
        field.value = fieldsToUpdate[field.name];
      }
    });

    if (validator.form()) {
      $.post($form.attr('action'), $.param(formData), function (response) {
        window.location.reload();
      }, 'json').error(function (jqxhr, textStatus, errorThrown) {
        var json = jQuery.parseJSON(jqxhr.responseText);
        $form.find('.alert-danger').html(Translator.trans(json.message)).show();
        drag.initDragCaptcha();
      });
    }
  }
});

$('.receive-modal').click();


if ($('.js-sts-login-link').length) {
  new Face({
    element: $('.js-login-main'),
  });
}