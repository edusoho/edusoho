let $form = $('#thread-post-form');

let validator = $form.validate({
  rules: {
    'post[content]': {
      required: true
    }
  }
});

$('.js-btn-thread-save').click((event) => {
  if (validator.form()) {
    $(event.currentTarget).button('loading');
    $form.submit();
  }
});

let editor = CKEDITOR.replace('post_content', {
  toolbar: 'Thread',
  fileSingleSizeLimit: app.fileSingleSizeLimit,
  filebrowserImageUploadUrl: $('#post_content').data('imageUploadUrl'),
  height: 300
});

editor.on('change', () => {
  $('#post_content').val(editor.getData());
  validator.form();
});

editor.on('blur', () => {
  $('#post_content').val(editor.getData());
  validator.form();
});

