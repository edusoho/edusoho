import notify from 'common/notify';

$.validator.addMethod('spaceNoSupport', function (value, element) {
  return value.indexOf(' ') < 0;
}, $.validator.format(Translator.trans('validate.have_spaces')));

$('#settings-password-form').validate({
  highlight: function(element) {
    $(element).css('border-bottom', '1px solid red');
    if (element.name === 'newPassword') {
      $('.js-new-password-tip').hide();
    }
  },
  success: function (label, element) {
    $(element).css('border-bottom', '1px solid #e1e1e1');
    if (element.name === 'newPassword') {
      $('.js-new-password-tip').show();
    }
  },
  currentDom: '#password-save-btn',
  ajax: true,
  rules: {
    'currentPassword': {
      required: true,
    },
    'newPassword': {
      required: true,
      visible_character: true,
      spaceNoSupport: true,
      check_password_high: true,
    },
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

$('.js-new-password-open-eye').on('click', function () {
  $('#form_newPassword').attr('type', 'password');
  $('.js-new-password-open-eye').hide();
  $('.js-new-password-close-eye').show();
})

$('.js-new-password-close-eye').on('click', function () {
  $('#form_newPassword').attr('type', 'text');
  $('.js-new-password-close-eye').hide();
  $('.js-new-password-open-eye').show();
})

$('.js-current-password-open-eye').on('click', function () {
  $('#form_currentPassword').attr('type', 'password');
  $('.js-current-password-open-eye').hide();
  $('.js-current-password-close-eye').show();
})

$('.js-current-password-close-eye').on('click', function () {
  $('#form_currentPassword').attr('type', 'text');
  $('.js-current-password-close-eye').hide();
  $('.js-current-password-open-eye').show();
})