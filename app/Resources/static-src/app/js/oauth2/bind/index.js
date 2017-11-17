const $form = $('#third-party-bind-form');
const $btn = $('.js-submit-btn');

let validator = $form.validate({
  rules: {
    password: {
      required: true,
    },
  },
});


$form.keypress(function (e) {
  if (e.which == 13) {
    $btn.trigger('click');
    e.preventDefault();
  }
});


$btn.click((event) => {
  if (validator.form()) {
    $.post($form.attr('url'), (response) => {
      console.log(response);
    })
  }
});