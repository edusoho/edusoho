import axis from 'common/axis';

const $form = $('#third-party-login-form');
const $btn = $('.js-submit-btn');
let validator = $form.validate({
  rules: {
    'way': {
      required: true,
      email_or_mobile_check: true,
    },
  },
  messages: {
    required: Translator.trans('validate.phone_and_email_input.message')
  },
});

$form.keypress(function (e) {
  if (e.which == 13) {
    $btn.trigger('click');
    e.preventDefault();
  }
});


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


const IsBind = bindCheck($('input[name="way"]'));

$btn.click((event) => {
  if (validator.form() && !IsBind) {
    $form.submit();
    window.location.href = $btn.data('url');
  } else if (validator.form() && IsBind) {
    window.location.href = $btn.data('create-url');
  }
});

