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
    $btn.button('loading');
    $.post($form.attr('action'), $form.serialize(), function (response) {
    }, 'json').error(function (jqxhr, textStatus, errorThrown) {
    });
  }
});