/**
 * [description]
 * @param  {[string]} $item [te]
 * @return {[type]}       [description]
 */
export const initEditor = ($item) => {
  var editor = CKEDITOR.replace('text-content-field', {
    toolbar: 'Full',
    filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
    filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
    allowedContent: true,
    height: 300
  });
  editor.on( 'change', () => {    
    $item.val(editor.getData());
  });
}


