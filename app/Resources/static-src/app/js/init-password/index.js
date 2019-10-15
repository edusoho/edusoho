let $form = $('#init-password-form');

$.validator.addMethod('spaceNoSupport', function (value, element) {
  return value.indexOf(' ') < 0;
}, $.validator.format(Translator.trans('validate.have_spaces')));

let validator = $form.validate({
  rules: {
    newPassword: {
      required: true,
      minlength: 5,
      maxlength: 20,
      spaceNoSupport: true
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