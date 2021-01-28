import notify from 'common/notify';

let $form = $('#refund-apply-form');
let $btn = $('#refund-apply-btn');
let validator = $form.validate({
  rules: {
    reason : {
      required: true,
    }
  },
  ajax: true,
  currentDom: '#refund-apply-btn',
  submitSuccess: function () {
    $('#modal').modal('hide');
    window.location.href = $form.data('redirect');
  }
});
