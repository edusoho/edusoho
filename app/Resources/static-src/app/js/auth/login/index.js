import Face from './face';

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


if ($('.js-sts-login-link').length) {
  new Face({
    element: $('.js-login-main'),
  });
}