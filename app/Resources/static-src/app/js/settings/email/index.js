import notify from 'common/notify';

$('#setting-email-form').validate({
  currentDom: '#submit-btn',
  ajax: true,
  rules: {
    'password': 'required',
    'email': 'required es_email'
  },
  submitSuccess(data) {
    notify('success', Translator.trans(data.message));
    
    $('.modal').modal('hide');
    window.location.reload();
  },
  submitError(data) {
    notify('danger',  Translator.trans(data.responseJSON.message));
  }
});
