/**
 * [description]
 * @param  {[string]} $item [te]
 * @return {[type]}       [description]
 */
export const initEditor = ($item, validator) => {
  var editor = CKEDITOR.replace('text-content-field', {
    toolbar: 'Full',
    filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
    filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
    allowedContent: true,
    height: 300
  });
  editor.on('change', () => {
    $item.val(editor.getData());
  });

  editor.on('blur', () => {
    $item.val(editor.getData());//ie11
    if (validator) {
      validator.form();
    }
  });

  return editor;
};