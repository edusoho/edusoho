// import Text from './text';
// new Text();

window.ltc.loadCss();
let load = window.ltc.load('jquery', 'validate', 'editor');
load.then(function(){
  const DEFAULTS = {
    element: '#text-content-field',
    fileSingleSizeLimit: 2,
    height: 300,
    target: '[name="content"]',
    validator: ''
  };

  let editor = CKEDITOR.replace(DEFAULTS.element.replace('#', ''), {
    toolbar: 'Task',
    fileSingleSizeLimit: DEFAULTS.fileSingleSizeLimit,
    filebrowserImageUploadUrl: $(DEFAULTS.element).data('imageUploadUrl'),
    filebrowserFlashUploadUrl: $(DEFAULTS.element).data('flashUploadUrl'),
    allowedContent: true,
    height: DEFAULTS.height,
  });
  
  editor.on('change', () => {
    console.log('change');
    $(DEFAULTS.target).val(editor.getData());
    if (DEFAULTS.validator) {
      DEFAULTS.validator.form();
    }
  });

  //fix ie11 中文输入
  editor.on('blur', () => {
    console.log('blur');
    $(DEFAULTS.target).val(editor.getData());
    if (DEFAULTS.validator) {
      DEFAULTS.validator.form();
    }
  });
});