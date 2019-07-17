export default class showCkEditor {
  constructor() {
    this.titleEditorToolBarName = 'Minimal';
    this.titleFieldId = 'question-stem-field';
    this.init();
  }
  init() {
    this.initTitleEditor();
  }


  initTitleEditor(validator) {
    let $target = $('#' + this.titleFieldId);
    let editor = CKEDITOR.replace(this.titleFieldId, {
      toolbar: this.titleEditorToolBarName,
      fileSingleSizeLimit: app.fileSingleSizeLimit,
      filebrowserImageUploadUrl: $target.data('imageUploadUrl'),
      height: $target.height()
    });

    editor.on('change', () => {
      $target.val(editor.getData());
      console.log(editor.getData());
    });
    editor.on('blur', () => {
      $target.val(editor.getData());
      console.log(editor.getData());
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