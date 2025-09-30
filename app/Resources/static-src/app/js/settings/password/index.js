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

const needStrongPassword = $('#form_newPassword').data('strong');
if (needStrongPassword) {
  $('#form_newPassword').rules('add', {
    required: true,
    visible_character: true,
    spaceNoSupport: true,
    password_strong: true,
  });
} else {
  $('#form_newPassword').rules('add', {
    required: true,
    visible_character: true,
    spaceNoSupport: true,
    password_normal: true,
  });
}


$('.js-new-password-open-eye').on('click', function () {
  $('#form_newPassword').attr('type', 'password');
  $('.js-new-password-open-eye').hide();
  $('.js-new-password-close-eye').show();
});

$('.js-new-password-close-eye').on('click', function () {
  $('#form_newPassword').attr('type', 'text');
  $('.js-new-password-close-eye').hide();
  $('.js-new-password-open-eye').show();
});

$('.js-current-password-open-eye').on('click', function () {
  $('#form_currentPassword').attr('type', 'password');
  $('.js-current-password-open-eye').hide();
  $('.js-current-password-close-eye').show();
});

$('.js-current-password-close-eye').on('click', function () {
  $('#form_currentPassword').attr('type', 'text');
  $('.js-current-password-close-eye').hide();
  $('.js-current-password-open-eye').show();
});

$('.js-confirm-password-open-eye').on('click', function () {
  $('#form_confirmPassword').attr('type', 'password');
  $('.js-confirm-password-open-eye').hide();
  $('.js-confirm-password-close-eye').show();
});

$('.js-confirm-password-close-eye').on('click', function () {
  $('#form_confirmPassword').attr('type', 'text');
  $('.js-confirm-password-close-eye').hide();
  $('.js-confirm-password-open-eye').show();
});