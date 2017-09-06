import notify from 'common/notify';

let $form = $('#setup-password-form');

$form.validate({
  rules: {
    ajax: true,
    currentDom: '#form-submit',
    'newPassword': {
      required: true,
      minlength: 5,
      maxlength: 20,
    },
    'confirmPassword': {
      required: true,
      equalTo: '#form_newPassword',
    },
    submitSuccess(data) {
      notify('success', Translator.trans(data.message));
      
      console.log(1111);
      // $('.modal').modal('hide');
      // window.location.reload();
    },
    submitError(data) {
      notify('danger',  Translator.trans(data.responseJSON.message));
    }
  }
});