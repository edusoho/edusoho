export let initEditor = (options) => {
  var editor = CKEDITOR.replace(options.replace, {
    toolbar: options.toolbar,
    fileSingleSizeLimit: app.fileSingleSizeLimit,
    filebrowserImageUploadUrl: $('#' + options.replace).data('imageUploadUrl'),
    allowedContent: true,
    height: 300
  });
  editor.on('change', () => {
    $('#' + options.replace).val(editor.getData());
  });
  editor.on('blur', () => {
    $('#' + options.replace).val(editor.getData());
  });
};