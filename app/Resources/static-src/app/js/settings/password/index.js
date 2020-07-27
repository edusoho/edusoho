import notify from 'common/notify';

$.validator.addMethod('spaceNoSupport', function (value, element) {
  return value.indexOf(' ') < 0;
}, $.validator.format(Translator.trans('validate.have_spaces')));

let passwordRules = function () {
  let rules = {
    required: true,
    visible_character: true,
    spaceNoSupport: true,
  };
  let passwordLevel = $('#password_level').val();
  rules[`check_password_${passwordLevel}`] = true;

  return rules;
};

$('#settings-password-form').validate({
  currentDom: '#password-save-btn',
  ajax: true,
  rules: {
    'currentPassword': {
      required: true,
    },
    'newPassword': passwordRules(),
    'confirmPassword': {
      required: true,
      equalTo: '#form_newPassword',
      visible_character: true
    }
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