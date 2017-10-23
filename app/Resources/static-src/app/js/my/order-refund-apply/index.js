import notify from 'common/notify';

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
    $.post($form.data('url'), $form.serialize(), function (response) {
      window.location.href = $form.data('redirect');
    }, 'json').error(function (jqxhr, textStatus, errorThrown) {
      notify.error('apply error!');
    });
  }
});