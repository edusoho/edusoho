let $form = $('#password-reset-update-form');
let validator = $form.validate({
  rules: {
    'form[password]': {
      required: true,
      check_password_high: true,
    },
    'form[confirmPassword]': {
      required: true,
      equalTo: '#form_password'
    }
  }
});