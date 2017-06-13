let $form = $('#award-point-exchange-form');
let validator = $form.validate({
  rules: {
    tel: {
      required: true
    },
    mail: {
      required: true,
      email: true
    },
    address: {
      required: true
    }
  }
});

$('.js-btn-pay').click((event) => {
  if (validator.form()) {
    $(event.currentTarget).button('loadding');
    $form.submit();
  }
});