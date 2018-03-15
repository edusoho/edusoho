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

enterSubmit($form, $btn);