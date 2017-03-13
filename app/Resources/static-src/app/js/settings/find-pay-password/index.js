let validator = $('#settings-find-pay-password-form').validate({
  rules: {
    'answer': {
      required: true,
      maxlength: 20
    }
  }
})

$('#answer-question-btn').on('click', (event) => {
  const $this = $(event.currentTarget);
  if (validator.form()) {
    $this.button('loading');
    $('#settings-find-pay-password-form').submit();
  }
})