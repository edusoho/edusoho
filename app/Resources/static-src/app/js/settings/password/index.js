$('#settings-password-form').validate({
  currentDom: '#password-save-btn',
  ajax: true,
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
  },
  submitSuccess() {
    // $('.modal').modal('hide');
    // window.location.reload();
  }
})