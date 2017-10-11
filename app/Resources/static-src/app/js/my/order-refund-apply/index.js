$form = $('#refund-apply-form');
let validator = $form.validate({
  rules: {
    'form[reason]': {
      required: true,
    }
  }
});