import Face from '../login/face';

let $form = $('#login-ajax-form');
let $btn = $('.js-submit-login-ajax');
let validator = $form.validate({
  rules: {
    _username: {
      required: true,
    },
    _password: {
      required: true,
    }
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
    });
  }
});


if ($('.js-sts-login-link').length) {
  new Face({
    element: $('.js-login-modal'),
    target: '.js-login-form, .modal-footer',
  });
}

