let $form = $('#setup-password-form');
let $btn = $('#password-save-btn');

if ($form.length) {
  let validator = $form.validate({
    rules: {
      'form[newPassword]': {
        required: true,
        minlength: 5,
        maxlength: 20,
      },
      'form[confirmPassword]': {
        required: true,
        equalTo: '#form_newPassword',
      }
    }
  });

  $btn.click(() => {
    if (validator.form()) {
      $btn.button('loadding');
      $form.submit();
    }
  })
}

