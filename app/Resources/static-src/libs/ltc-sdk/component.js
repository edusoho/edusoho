import { script } from './utils';

const resource = {
  ckeditor: '/static-dist/libs/es-ckeditor/ckeditor.js'
}

export const ckeditor = (callback) => {
  script([resource.ckeditor], callback)
}

export const ckeditorInit = async (options) => {
  let editor;

  const DEFAULTS = {
    element: '#text-content-field',
    fileSingleSizeLimit: 2,
    height: 300,
    target: '[name="content"]',
    validator: ''
  }

  Object.assign(DEFAULTS, options);
  
  await new Promise((resolve, reject) => {
    script([resource.ckeditor], () => {
      editor = CKEDITOR.replace(DEFAULTS.element.replace('#', ''), {
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
      
      console.log(editor, 'editor1');

      resolve();
    });
  })

  return editor;
}