let validator = $('#settings-password-form').validate({
  rules: {
    'form[currentPassword]': {
      required: true,
    },
    'form[newPassword]': {
      required: true,
      minlength: 5,
      maxlength: 20,
      visible_character: true
    },
    'form[confirmPassword]': {
      required: true,
      equalTo: '#form_newPassword',
      visible_character: true
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