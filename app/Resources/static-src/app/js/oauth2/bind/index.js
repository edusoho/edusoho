import { enterSubmit } from 'app/common/form';

const $form = $('#third-party-bind-form');
const $btn = $('.js-submit-btn');

let validator = $form.validate({
  currentDom: $btn,
  ajax: true,
  rules: {
    password: {
      required: true,
    }
  },
  submitSuccess(data) {
    if(data.success === 0) {
      if (!$('.js-password-error').length) {
        $btn.prev().addClass('has-error').append(`<p id="password-error" class="form-error-message js-password-error">${data.message}</p>`);
      }
    } else {
      window.location.href = data.url;
    }
  },
});


$('#password').focus(() => {
  $('.js-password-error').remove();
});

$('.js-password-open-eye').on('click', function () {
  $('#password').attr('type', 'password');
  $('.js-password-open-eye').hide();
  $('.js-password-close-eye').show();
})

$('.js-password-close-eye').on('click', function () {
  $('#password').attr('type', 'text');
  $('.js-password-close-eye').hide();
  $('.js-password-open-eye').show();
})

enterSubmit($form, $btn);