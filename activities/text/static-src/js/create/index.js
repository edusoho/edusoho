// import Text from './text';
// new Text();

window.ltc.loadCss();
let load = window.ltc.load('jquery', 'validate', 'editor');
load.then(function(){
  let $content = $('#text-content-field');
  let editor = CKEDITOR.replace('text-content-field', {
    toolbar: 'Task',
    fileSingleSizeLimit: 2,
    filebrowserImageUploadUrl: $content.data('imageUploadUrl'),
    filebrowserFlashUploadUrl: $content.data('flashUploadUrl'),
    allowedContent: true,
    height: 300,
  });
  
  let validate = $('#step2-form').validate({
    rules: {
      title: {
        required: true,
        maxlength: 50,
        trim: true,
        course_title: true,
      },
      content: {
        required: true,
        trim: true,
      },
    },
  });

  editor.on('change', () => {
    $content.val(editor.getData());
    validate.form();
  });

  editor.on('blur', () => {
    $content.val(editor.getData());
    validate.form();
  });
  

  window.ltc.on('getActivity', (msg) => {
    if (validate.form()) {
      window.ltc.emit('returnActivity', {valid:true,data:$('#step2-form').serializeObject()});
    }
  });

}).catch(function(e){

});