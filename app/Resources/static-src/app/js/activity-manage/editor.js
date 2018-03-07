export const initEditor = ($item, validator) => {
  
  var editor = CKEDITOR.replace('text-content-field', {
    toolbar: 'Task',
    fileSingleSizeLimit: app.fileSingleSizeLimit,
    filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
    filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
    allowedContent: true,
    height: 300,
  });
  
  editor.on('change', () => {
    console.log('change');
    $item.val(editor.getData());
    if (validator) {
      validator.form();
    }
  });

  //fix ie11 中文输入
  editor.on('blur', () => {
    console.log('blur');
    $item.val(editor.getData());
    if (validator) {
      validator.form();
    }
  });

  return editor;
};