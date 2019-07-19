export default class showCkEditor {
  constructor(options) {
    this.titleEditorToolBarName = 'Minimal';
    this.fieldId = options.fieldId;
    this.oldFieldId = options.oldFieldId;
    this.init();
  }

  init() {
    this.initEditor();
  }

  initEditor(validator) {
    let $target = $('#' + this.fieldId);
    let editor = null;

    if (CKEDITOR.instances[this.oldFieldId]) {
      editor = CKEDITOR.instances[this.oldFieldId];
      let $oldTarget = $('#' + this.oldFieldId);
      $oldTarget.addClass('hidden');
      $(`#cke_${this.oldFieldId}`).addClass('hidden');
      let data = editor.getData().replace(/<\s?img[^>]*>/gi, '【图片】').replace(/<p>|<\/p>/gi, '');
      $oldTarget.prev().removeClass('hidden').val(data);
    }

    if (CKEDITOR.instances[this.fieldId]) {
      editor = CKEDITOR.instances[this.fieldId];
      $(`#cke_${this.fieldId}`).removeClass('hidden');
    } else {
      editor = CKEDITOR.replace(this.fieldId, {
        toolbar: this.titleEditorToolBarName,
        fileSingleSizeLimit: app.fileSingleSizeLimit,
        filebrowserImageUploadUrl: $target.data('imageUploadUrl'),
        height: $target.height(),
        startupFocus: true,
      });
    }
    editor.focus();

    // let self = this;
    editor.on('change', () => {
      $target.val(editor.getData());
      console.log(editor.getData());
    });
    editor.on('blur', () => {
      $target.val(editor.getData());
      console.log(editor.getData());
      // $target.addClass('hidden');
      // $(`#cke_${self.fieldId}`).addClass('hidden');
      // $('.js-upload-stem-attachment').addClass('hidden');
      // let data = editor.getData().replace(/<\s?img[^>]*>/gi, '【图片】').replace(/<p>|<\/p>/gi, '');
      // $target.prev().removeClass('hidden').val(data);
    });
  }

  // initAnalysisEditor() {
  //   let $target = $('#' + this.analysisFieldId);
  //   let editor = CKEDITOR.replace(this.analysisFieldId, {
  //     toolbar: this.titleEditorToolBarName,
  //     fileSingleSizeLimit: app.fileSingleSizeLimit,
  //     filebrowserImageUploadUrl: $target.data('imageUploadUrl'),
  //     height: $target.height()
  //   });

  //   editor.on('change', () => {
  //     $target.val(editor.getData());
  //   });
  // }
}