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
    let url = $btn.data('goto-url');

    $.get(url).done(function(html) {
      $('#modal').html(html);
    })
    
    // notify('success', Translator.trans(data.message));
    // $('.modal').modal('hide');
    
    // setTimeout(function() {
    //   window.location.reload();
    // }, 3000);
  },
  submitError(data) {
    notify('danger',  Translator.trans(data.responseJSON.message));
  }
});