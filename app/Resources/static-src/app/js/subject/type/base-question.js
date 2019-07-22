import AttachmentActions from '../../attachment/widget/attachment-actions';

class BaseQuestion {
  constructor($form) {
    this.$form = $form;
    this.titleFieldId = 'question-stem-field';
    this.validator = null;
    this.titleEditorToolBarName = 'Minimal';
    this._init();
    this.attachmentActions = new AttachmentActions($form);
  }

  _init() {
    this._initEvent();
    this._initValidate();
  }

  _initEvent() {
    this.$form.on('click', '.subject-button', event => this.submitForm(event));
  }

  submitForm(event) {
    const $editItem = $(event.currentTarget).parents('.subject-edit-item');
    const $item = $('.subject-item.hidden');

    if (this.validator.form()) {
      $(event.currentTarget).button('loading');
      $editItem.remove();
      $item.removeClass('hidden');
      // this.$form.submit();
    }
  }

  _initValidate() {
    let validator = this.$form.validate({
      onkeyup: false,
      rules: {
        difficulty: {
          required: true,
        },
        stem: {
          required: true,
        },
        // score: {
        //   required: true,
        //   number: true,
        //   max: 999,
        //   min: 0,
        //   es_score: true
        // }
      },
      messages: {
        difficulty: Translator.trans('course.question.create.difficulty_required_error_hint')
      }
    });
    this.validator = validator;
  }

  initTitleEditor(validator) {
    let self = this;
    let $target = $('#' + self.titleFieldId);
    let $input = $target.prev();
    let editor = CKEDITOR.replace(self.titleFieldId, {
      toolbar: self.titleEditorToolBarName,
      fileSingleSizeLimit: app.fileSingleSizeLimit,
      filebrowserImageUploadUrl: $target.data('imageUploadUrl'),
      height: $target.height()
    });

    editor.on('change', () => {
      $target.val(editor.getData());
      validator.form();
      $input.val($(self.replacePicture(editor.getData())).text());
    });
    editor.on('blur', () => {
      $target.val(editor.getData());
      validator.form();
      $input.val($(self.replacePicture(editor.getData())).text());
    });
    editor.on('instanceReady', function() {
      this.focus();

      $('[data-role="edit"]').one('click', function() {
        $input.val($(self.replacePicture(editor.getData())).text());
        editor.destroy();
        $target.hide();
        $input.show();
      });
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

  replacePicture(str) {
    return str.replace(/<img [^>]*src=['"]([^'"]+)[^>]*>/gi, '[图片]');
  }

  set titleEditorToolBarName(toolbarName) {
    this._titleEditorToolBarName = toolbarName;
  }

  get titleEditorToolBarName() {
    return this._titleEditorToolBarName;
  }
}

export default BaseQuestion;