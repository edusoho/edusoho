let $modal = $('#modal');
let $form = $('#unbind-form');
let $btn = $('#unbind-btn');

let validator = $form.validate({
  rules: {
    mobile: {
      required: true,
      phone: true
    }
  }
});

$btn.click(() => {
  if (validator.form()) {
    $btn.button('loading');
    $modal.modal('hide');
    let payAgreementId = $('input[name=\'payAgreementId\']').val();
    $.post($form.attr('action'), $form.serialize(), function (response) {
      if (response.success) {
        $('#unbind-bank-' + payAgreementId).remove();
        Notify.success(response.message);
      } else {
        Notify.danger(response.message);
      }
    });
  }
});



