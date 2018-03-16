import notify from 'common/notify';

let $btn = $('#submit-btn');

$('#setting-email-form').validate({
  currentDom: '#submit-btn',
  ajax: true,
  rules: {
    'password': 'required',
    'email': 'required es_email'
  },
  submitSuccess(data) {
    $('#modal').html(data);
  },
  submitError(data) {
    notify('danger',  Translator.trans(data.responseJSON.message));
  }
});