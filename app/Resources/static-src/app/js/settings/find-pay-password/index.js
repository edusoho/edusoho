import notify from 'common/notify';

let validator = $('#settings-find-pay-password-form').validate({
  rules: {
    'answer': {
      required: true,
      maxlength: 20
    }
  },
});

$('#answer-question-btn').on('click', (event) => {
  const $this = $(event.currentTarget);
  if (validator.form()) {
    $this.button('loading');
    $('#settings-find-pay-password-form').submit();
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