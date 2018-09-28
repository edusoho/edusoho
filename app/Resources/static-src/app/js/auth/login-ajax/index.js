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


$('.js-login-modal').on('click', '.js-sts-login-link', () => {
  const $qrcodeWrap = $('.js-sts-login');
  $.ajax({
    type: 'post',
    url: $qrcodeWrap.data('url'),
    dataType: 'json',
    success: (data) => {
      console.log(data);
      $qrcodeWrap.find('.js-sts-login-qrcode img').attr('src', data.qrcode);
      $('.js-login-modal .modal-footer, .js-login-main, .js-sts-login').toggleClass('hidden');
    }
  });
});

$('.js-sts-login').on('click', '.js-login-back', () => {
  $('.js-login-modal .modal-footer, .js-login-main, .js-sts-login').toggleClass('hidden');
});
