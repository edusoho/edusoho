import notify from 'common/notify';

$('#settings-pay-password-form').validate({
  currentDom: '#password-save-btn',
  ajax: true,
  rules: {
    'oldPayPassword': {
      required: true,
      minlength: 5,
      maxlength: 20
    },
    'newPayPassword': {
      required: true,
      minlength: 5,
      maxlength: 20
    },
    'confirmPayPassword': {
      required: true,
      equalTo: '#form_newPayPassword'
    }
  },
  submitSuccess(data) {
    notify('success', Translator.trans(data.message));

    $('.modal').modal('hide');
    window.location.reload();
  },
  submitError(data) {
    notify('danger', Translator.trans(data.responseJSON.message));
  }
});