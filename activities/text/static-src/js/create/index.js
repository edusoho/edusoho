// import Text from './text';
// new Text();

window.ltc.loadCss();
let load = window.ltc.load('jquery', 'validate', 'editor');
load.then(function(){
  $.fn.serializeObject = function()
  {
    let o = {};
    let a = this.serializeArray();
    $.each(a, function() {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  };

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

  window.ltc.on('getActivity', (msg) => {
    window.ltc.messenger.sendToParent('returnActivity', {valid:true,data:$('#step2-form')});
  });

}).catch(function(e){

});