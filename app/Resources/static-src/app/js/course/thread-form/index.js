let $form = $('#thread-form');
let validator = $form.validate({
  rules: {
    'thread[title]': {
      required: true,
      trim: true,
      maxlength: 30,
    },
    'thread[content]': {
      required: true,
      maxlength: 1000,
    }
  }
});

$('.js-btn-thread-save').click((event) => {
  if (validator.form()) {
    $(event.currentTarget).button('loading');
    $form.submit();
  }
});

let editor = CKEDITOR.replace('thread_content', {
  toolbar: 'Thread',
  fileSingleSizeLimit: app.fileSingleSizeLimit,
  filebrowserImageUploadUrl: $('#thread_content').data('imageUploadUrl')
});

editor.on('change', () => {
  $('#thread_content').val(editor.getData());
});
editor.on('blur', () => {
  $('#thread_content').val(editor.getData());
  validator.form();
});