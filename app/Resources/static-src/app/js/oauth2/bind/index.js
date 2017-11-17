import notify from 'common/notify';

const $form = $('#third-party-bind-form');
const $btn = $('.js-submit-btn');

let validator = $form.validate({
  rules: {
    password: {
      required: true,
    },
  }
});


$form.keypress(function (e) {
  if (e.which == 13) {
    $btn.trigger('click');
    e.preventDefault();
  }
});


$btn.click((event) => {
  if (!validator.form()) {
    return;
  }
  $.post($form.attr('action'), $form.serialize(), (response) => {
      if (response.success === 0) {
      notify('danger', response.message);
    } else {
      window.location.href = response.url;
    }
  })
});