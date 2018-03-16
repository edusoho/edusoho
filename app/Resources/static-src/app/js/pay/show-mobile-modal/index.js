import notify from 'common/notify';
let $modal = $('#modal'), $form = $('#unbind-form'), $btn = $('#unbind-btn');

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
        notify('success', response.message);
      } else {
        notify('danger', response.message);
      }
    });
  }
});



