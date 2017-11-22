import { enterSubmit } from 'common/utils';

const $form = $('#third-party-login-form');
const $btn = $('.js-submit-btn');

let validator;

if ($('#email').length) {
  validator = $form.validate({
    rules: {
      account: {
        required: true,
        email: true,
      },
    },
    messages: {
      required: Translator.trans('validate.valid_email_input.message'),
    },
  });
}

if ($('#mobile').length) {
  validator = $form.validate({
    rules: {
      account: {
        required: true,
        phone: true,
      },
    },
    messages: {
      required: Translator.trans('validate.phone.message')
    },
  });
}

if ($('#mobileOrEmail').length) {
  validator = $form.validate({
    rules: {
      account: {
        required: true,
        email_or_mobile_check: true,
      },
    },
    messages: {
      required: Translator.trans('validate.phone_and_email_input.message')
    },
  });
}


enterSubmit($form, $btn);

$btn.click((event) => {
  let type;
  const reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

  if (validator.form()) {
    $(event.target).button('loading');
    let isEmail = reg_email.test($("input[name='account']").val());
    type = isEmail ? 'email' : 'mobile';
    $('#accountType').val(type);
    $form.submit();
  }
})
