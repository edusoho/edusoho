let $form = $('#init-password-form');

$.validator.addMethod('spaceNoSupport', function (value, element) {
  return value.indexOf(' ') < 0;
}, $.validator.format(Translator.trans('validate.have_spaces')));

let passwordRules = function () {
  let rules = {
    required: true,
    spaceNoSupport: true,
  };
  let passwordLevel = $('#password_level').val();
  rules[`check_password_${passwordLevel}`] = true;

  return rules;
};

let validator = $form.validate({
  rules: {
    newPassword: passwordRules(),
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