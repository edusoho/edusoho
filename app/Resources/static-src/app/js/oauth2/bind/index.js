import { enterSubmit } from 'common/utils';

const $form = $('#third-party-bind-form');
const $btn = $('.js-submit-btn');

let validator = $form.validate({
  rules: {
    password: {
      required: true,
    }
  }
});

$('#password').focus(() => {
  $('.js-password-error').remove();
})

enterSubmit($form, $btn);

$btn.click((event) => {
  if (!validator.form()) {
    return;
  }
  const $target = $(event.target);
  $target.button('loading');
  $.post($form.attr('action'), $form.serialize(), (response) => {
    if (response.success === 0) {
      $target.button('reset');
      if (!$('.js-password-error').length) {
        $target.prev().addClass('has-error').append(`<p id="password-error" class="form-error-message js-password-error">${response.message}</p>`);
      }
    } else {
      window.location.href = response.url;
    }
  })
});