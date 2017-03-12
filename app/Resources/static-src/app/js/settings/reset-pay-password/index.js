let validator = $('#settings-pay-password-form').validate({
  rules: {
    'form[oldPayPassword]': {
      required: true,
      minlength: 5,
      maxlength: 20
    },
    'form[newPayPassword]': {
      required: true,
      minlength: 5,
      maxlength: 20
    },
    'form[confirmPayPassword]': {
      required: true,
      equalTo: '#form_newPayPassword'
    }
  }
})

$('#password-save-btn').on('click', (event) => {
  const $this = $(event.currentTarget);
  if (validator.form()) {
    $this.button('loading');
    $('#settings-pay-password-form').submit();
  }
})