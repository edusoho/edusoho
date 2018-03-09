import notify from 'common/notify';
let $form = $('#pay-password-reset-update-form');

let validator = $form.validate({
  rules: {
    'form[currentUserLoginPassword]': {
      required: true,
    },
    'form[payPassword]': {
      required: true,
      minlength: 5,
      maxlength: 20
    },
    'form[confirmPayPassword]': {
      required: true,
      equalTo: '#form_payPassword'
    }
  },
});

console.log(validator);

$('#payPassword-save-btn').on('click', (event) => {
  const $this = $(event.currentTarget);
  if (validator.form()) {
    $this.button('loading');
    $form.submit();
  }
});

let messageDanger = $('.alert-danger').text();
let messageSuccess = $('.alert-success').text();

if (messageDanger) {
  notify('danger', Translator.trans(messageDanger));
}

if (messageSuccess) {
  notify('success', Translator.trans(messageSuccess));
}