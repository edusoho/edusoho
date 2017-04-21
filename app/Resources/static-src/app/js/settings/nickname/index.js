let validator = $('#nickname-form').validate({
  rules: {
    nickname: {
      required:true,
      minlength: 4,
      maxlength: 18,
      nickname: true,
      chinese_alphanumeric: true,
      es_remote: {
        type: 'get',
      }
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