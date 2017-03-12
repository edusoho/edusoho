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

$('#nickname-btn').on('click', () => {
  if (validator.form()) {
    $('#nickname-form').submit();
  }
})