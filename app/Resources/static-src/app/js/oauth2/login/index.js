const $form = $('#third-party-login-form');
const $btn = $('.js-submit-btn');

$form.keypress(function (e) {
  if (e.which == 13) {
    $btn.trigger('click');
    e.preventDefault();
  }
});

const validateLogin = (validateName, rule, message) => {
  const $item = $('#'+ validateName);
  if (!$item.length) {
    return;
  }
  let validator = $form.validate({
    rules: {
      validateName: {
        required: true,
        rule: true,
      },
    },
    messages: {
      required: Translator.trans(message)
    },
  });
  return validator.form();
}

$btn.click((event) => {

  let isMobileOrEmail = validateLogin('mobileOrEmail', 'email_or_mobile_check', 'validate.phone_and_email_input.message');
  let isMobile = validateLogin('mobile', 'mobile', 'validate.phone.message');
  let isEmail = validateLogin('email', 'email', 'validate.valid_email_input.message');

  let isValidated = isMobileOrEmail || isMobile || isEmail;

  let reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

  let type;

  if (isValidated) {
    let isFinalEmail = reg_email.test($("input[name='account']").val());
    type = isFinalEmail ? 'email' : 'mobile';
    $('#accountType').val(type);
    $form.submit();
  }
})
