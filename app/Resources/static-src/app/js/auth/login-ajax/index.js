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


$('.js-login-modal').on('click', '.js-sts-login-link', (event) => {
  $('.modal-footer, .js-login-main').toggleClass('hidden');
  $('.js-sts-login').toggleClass('hidden');
});


$('.js-sts-login').on('click', '.js-login-back', (event) => {
  $('.modal-footer, .js-login-main').toggleClass('hidden');
  $('.js-sts-login').toggleClass('hidden');
});
