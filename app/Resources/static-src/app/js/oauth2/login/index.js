import { enterSubmit } from 'app/common/form';

const $form = $('#third-party-login-form');
const $btn = $('.js-submit-btn');

const validateMode = {
  mobile: {
    rules: {
      account: {
        required: true,
        phone: true,
      },
    },
    messages: {
      required: Translator.trans('validate.phone.message')
    }
  },
  email: {
    rules: {
      account: {
        required: true,
        maxlength: 32,
        email: true,
      },
    },
    messages: {
      required: Translator.trans('validate.valid_email_input.message')
    }
  },
  email_or_mobile: {
    rules: {
      account: {
        required: true,
        maxlength: 32,
        email_or_mobile_check: true,
      },
    },
    messages: {
      required: Translator.trans('validate.phone_and_email_input.message')
    }
  }
};

const ruleType = $('.js-third-party-type').data('type');
const validator = $form.validate(validateMode[ruleType]);

const isEmail = val => {
  const reg_email = /^([a-zA-Z0-9_.\-+])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

  return reg_email.test(val);
};

const trySubmit = () => {
  $.post($btn.data('tryBindUrl'), $form.serialize(), resp => {
    if (resp.success) {
      $form.submit();
    } else {
      $btn.button('reset');
      validator.showErrors({
        account: resp.message
      });
    }
  });
};

enterSubmit($form, $btn);

$btn.click((event) => {
  if (validator.form()) {
    $(event.target).button('loading');
    const type = isEmail($('input[name=account]').val()) ? 'email' : 'mobile';
    $('#accountType').val(type);
    trySubmit();
  }
});
