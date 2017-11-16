import axis from 'common/axis';

const $form = $('#third-party-login-form');
const $btn = $('.js-submit-btn');


$form.keypress(function (e) {
  if (e.which == 13) {
    $btn.trigger('click');
    e.preventDefault();
  }
});

const validateLogin = (validateName, rule, message) => {
  const $item = $('input[name='+ validateName +']');
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
  let isBind = bindCheck($item);
  let isValidated = validator.form();

  let type;

  if (isValidated && isBind) {
    type = 'bind';
  } else if (isValidated && !isBind) {
    type = 'create';
  } else {
    type = 'fail';
  }
  return type;
}

const bindCheck = ($dom) => {
  let url = $dom.data('url');
  let value = $dom.val();
  let isSuccess = 0;
  $.ajax({
    url: url,
    async: false,
    type: 'get',
    data: { value: value },
    dataType: 'json'
  })
  .success((response) => {
    if (axis.isObject(response)) {
      isSuccess = response.success;
    } else if (axis.isString(response)) {
      isSuccess = false;
    } else if (axis.isBoolean(response)) {
      isSuccess = response;
    }
  })
  return isSuccess;
}


$btn.click((event) => {
  let isMobileOrEmail = validateLogin('mobileOrEmail', 'email_or_mobile_check', 'validate.phone_and_email_input.message');

  let isMobile = validateLogin('mobile', 'mobile', 'validate.phone.message');

  let isEmail = validateLogin('email', 'email', 'validate.valid_email_input.message');

  console.log(isEmail);

})
// $btn.click((event) => {
//   if (validator.form() && !IsBind) {
//     $form.submit();
//     window.location.href = $btn.data('url');
//   } else if (validator.form() && IsBind) {
//     window.location.href = $btn.data('create-url');
//   }
// });