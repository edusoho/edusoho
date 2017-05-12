let $form = $('#login-ajax-form');
let validator = $form.validate({
  rules: {
    _username: {
      required: true,
    },
    _password: {
      required: true,
    }
  }
})
$('[type="submit"]').click(() => {
  if (validator.form()) {
    $.post($form.attr('action'), $form.serialize(), function (response) {
      window.location.reload();
    }, 'json').error(function (jqxhr, textStatus, errorThrown) {
      var json = jQuery.parseJSON(jqxhr.responseText);
      $form.find('.alert-danger').html(json.message).show();
    });
  }
})
