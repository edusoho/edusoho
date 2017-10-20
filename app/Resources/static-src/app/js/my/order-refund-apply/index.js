let $form = $('#refund-apply-form');
let $btn = $("#refund-apply-btn");
let validator = $form.validate({
  rules: {
    reason : {
      required: true,
    }
  }
});

$btn.click((event) => {
  if (validator.form()) {
    $.post($form.attr('action'), $form.serialize(), function (response) {
      $btn.button('loading');
      window.location.reload();
    }, 'json').error(function (jqxhr, textStatus, errorThrown) {
    });
  }
});