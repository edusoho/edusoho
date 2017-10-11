let $form = $('#refund-apply-form');
let validator = $form.validate({
  rules: {
    reason : {
      required: true,
    }
  }
});