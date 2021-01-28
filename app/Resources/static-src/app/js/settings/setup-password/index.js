import notify from 'common/notify';

let $form = $('#setup-password-form');

$form.validate({
  ajax: true,
  rules: {
    currentDom: '#form-submit',
    'form[newPassword]': {
      required: true,
      minlength: 5,
      maxlength: 20,
    },
    'form[confirmPassword]': {
      required: true,
      equalTo: '#form_newPassword',
    },
  },
  submitSuccess(res) {
    notify('success', Translator.trans(res.message));
    if ($form.data('targeType') == 'modal') {
      $('#modal').load($form.data('goto')).modal('show');
    } else {
      window.location.href = res.data.targetPath;
    }
    
    return false;
  },
  submitError(data) {
    notify('danger',  Translator.trans(data.responseJSON.message));
  }
});