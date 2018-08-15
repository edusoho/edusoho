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
  
  editor.on('change', () => {
    // $(DEFAULTS.target).val(editor.getData());
    // if (DEFAULTS.validator) {
    //   DEFAULTS.validator.form();
    // }
  });

  editor.on('blur', () => {
    // $(DEFAULTS.target).val(editor.getData());
    // if (DEFAULTS.validator) {
    //   DEFAULTS.validator.form();
    // }
  });

  window.ltc.on('next', (msg) => {
    window.ltc.messenger.sendToParent('nextReturn', {success:true,data:[{'name': 'finishDetail','value': '123456'}]});
  });

}).catch(function(e){

});