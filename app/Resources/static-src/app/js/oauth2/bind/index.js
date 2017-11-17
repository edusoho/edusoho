import notify from 'common/notify';
import { enterSubmit } from 'common/utils';

const $form = $('#third-party-bind-form');
const $btn = $('.js-submit-btn');

let validator = $form.validate({
  rules: {
    password: {
      required: true,
    },
  }
});


enterSubmit($form, $btn);

$btn.click((event) => {
  if (!validator.form()) {
    return;
  }
  $(event.target).button('loading');
  $.post($form.attr('action'), $form.serialize(), (response) => {
    if (response.success === 0) {
      notify('danger', response.message);
    } else {
      window.location.href = response.url;
    }
  })
});