const $form = $('#third-party-bind-form');
const $btn = $('.js-submit-btn');

let validator = $form.validate({
  currentDom: $btn,
  ajax: true,
  rules: {
    password: {
      required: true,
      es_remote: {
        type: 'post'
      },
    },
  },
  messages: {
    password: {
      required: Translator.trans('请输入密码')
    }
  },
  submitSuccess(data) {
    notify('success', Translator.trans(data.message));
  },
  submitError(data) {
    notify('danger',  Translator.trans(data.responseJSON.message));
  }
});


$form.keypress(function (e) {
  if (e.which == 13) {
    $btn.trigger('click');
    e.preventDefault();
  }
});


$btn.click((event) => {
  if (validator.form()) {
    window.location.href = $btn.data('url');
  }
});