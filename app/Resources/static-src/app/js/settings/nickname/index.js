let validator = $('#nickname-form').validate({
  rules: {
    'nickname': {
      chinese_alphanumeric: true,
      minlength: 2,
      maxlength: 9,
      nickname: true,
      nickname_remote: true
    }
  }
})

$('#nickname-btn').on('click', (event) => {
  const $this = $(event.currentTarget);
  if (validator.form()) {
    $this.button('loading');
    $('#nickname-form').submit();
  }
})