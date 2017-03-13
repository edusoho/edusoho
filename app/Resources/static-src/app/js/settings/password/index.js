let validator = $('#settings-password-form').validate({
  rules: {
    'form[currentPassword]': {
      required: true,
    },
    'form[newPassword]': {
      required: true,
      minlength: 5,
      maxlength: 20
    },
    'form[confirmPassword]': {
      required: true,
      equalTo: '#form_newPassword'
    }
  }
})

$('#password-save-btn').on('click', (event) => {
  const $this = $(event.currentTarget);
  if (validator.form()) {
    $this.button('loading');
    $('#settings-password-form').submit();
  }
})