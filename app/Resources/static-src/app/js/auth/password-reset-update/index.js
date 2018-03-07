let $form = $('#password-reset-update-form');
let validator = $form.validate({
  rules: {
    'form[password]': {
      required: true,
      minlength: 5,
      maxlength: 20
    },
    'form[confirmPassword]': {
      required: true,
      equalTo: '#form_password'
    }
  }
});