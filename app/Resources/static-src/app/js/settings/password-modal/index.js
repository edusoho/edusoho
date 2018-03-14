let $form = $('#settings-password-form');
let $modal = $('#modal');

let validator = $form.validate({
  rules: {
    'form[newPassword]': {
      required: true,
      minlength: 5,
      maxlength: 20
    },
    'form[confirmPassword]': {
      required: true,
      equalTo: '#form_newPassword'
    }
  }
});

$('.js-submit-form').off('click');
$('.js-submit-form').click(function () {
  if (validator.form()) {
    let data = $form.serialize();
    let targetUrl = $form.attr('action');
    $.post(targetUrl, data, function (html) {
      $modal.html(html);
    });
  }
});