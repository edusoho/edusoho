let $form = $('#init-password-form');

let validator = $form.validate({
  rules: {
    newPassword: {
      required: true,
      minlength: 5,
      maxlength: 20
    },
    confirmPassword: {
      required: true,
      equalTo: '#newPassword'
    }
  }
});


$('[type="submit"]').click(() => {
  if (validator.form()) {
    $form.submit();
  }
});