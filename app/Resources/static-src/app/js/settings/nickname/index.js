let validator = $('#nickname-form').validate({
  rules: {
    'nickname': {
      required: true,
      chinese_alphanumeric: true,
      byte_minlength: 4,
      byte_maxlength: 18,
      nickname: true,
      es_remote: {
        type: 'get',
      }
    }
  }
});

$('#nickname-btn').on('click', (event) => {
  const $this = $(event.currentTarget);
  if (validator.form()) {
    $this.button('loading');
    $('#nickname-form').submit();
  }
});