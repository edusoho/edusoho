let validator = $('#nickname-form').validate({
  rules: {
    'nickname': {
      chinese_alphanumeric: true,
      byte_minlength: 4,
      byte_maxlength: 18,
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