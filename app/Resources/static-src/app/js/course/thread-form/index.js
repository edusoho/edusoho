// var Validator = require('bootstrap.validator');
// require('common/validator-rules').inject(Validator);
// require('es-ckeditor');
// require('./common').run();

let $form = $('#thread-form');
let validator = $form.validate({
  rules: {
    'thread[title]': {
      required: true,
      trim: true,
    },
    'thread[content]': {
      required: true,
    }
  }
})

$('.js-btn-thread-save').click(() => {
  $('.js-btn-thread-save').button('loading');
  if (validator.form()) {
    $form.submit();
  }
})

let editor = CKEDITOR.replace('thread_content', {
  toolbar: 'Thread',
  filebrowserImageUploadUrl: $('#thread_content').data('imageUploadUrl')
});

editor.on('change', () => {
  $('#thread_content').val(editor.getData());
});
editor.on('blur', () => {
  $('#thread_content').val(editor.getData());
});

