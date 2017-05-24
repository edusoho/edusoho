let validator = $('#setting-email-form').validate({
  rules: {
    'form[password]': 'required',
    'form[email]': 'required es_email'
  }
})

$('#email-save-btn').on('click', (event) => {
  let $this = $(event.currentTarget);

  if(validator.form()) {
    $this.button('loading');
    $('#setting-email-form').submit();
  }
})

$('#send-verify-email').click(function() {
  let $btn = $(this);
  $btn.button('loading');
  $.post($btn.data('url'), function() {
    window.location.reload();
  });
});