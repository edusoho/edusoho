let $form = $('#refund-apply-form');
let validator = $form.validate({
  rules: {
    reason : {
      required: true,
    }
  }
});

$("#refund-apply-btn").on('click', function () {
  let $that = $(this);
  $that.button('loading');
});