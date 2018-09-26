let $form = $('#login-form');
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


$('.js-login-main').on('click', '.js-sts-login-link', (event) => {
  const $currentTarget = event.currentTarget;
  const $qrcodeWrap = $('.js-sts-login');
  $.ajax({
    type: 'post',
    url: $qrcodeWrap.data('url'),
    dataType: 'json',
    success: (data) => {
      $qrcodeWrap.find('.js-sts-login-qrcode img').attr('src', data.img);
      $('.js-login-main').toggleClass('hidden');
      $('.js-sts-login').toggleClass('hidden');
    }
  });
});


$('.js-sts-login').on('click', '.js-login-back', (event) => {
  $('.js-login-main').toggleClass('hidden');
  $('.js-sts-login').toggleClass('hidden');
});

