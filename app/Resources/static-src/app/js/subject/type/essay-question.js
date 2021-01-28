import BaseQuestion from './base-question';

class Essay extends BaseQuestion {
  constructor($form, object) {
    super($form, object);

    this.initTitleEditor(this.validator);

    this.answerFieldId = 'question-answer-field';
    this.$answerField = $('#' + this.answerFieldId);

    this.init();
  }

  init() {
    this.$answerField.rules('add', {
      required: true,
      messages: {
        required: Translator.trans('subject.essay_require_answer')
      }
    });
    this.$form.on('click', '[data-role="edit"]', event => this.answerClick(event));
  }

  answerClick(event) {
    let self = this;
    let $target = $(event.currentTarget);
    let $textarea = $target.next();
    $target.hide();
    let editor = CKEDITOR.replace($textarea.attr('id'), {
      toolbar: 'Minimal',
      fileSingleSizeLimit: app.fileSingleSizeLimit,
      filebrowserImageUploadUrl: $textarea.data('imageUploadUrl'),
      height: $textarea.height()
    });

    editor.on('change', () => {
      $textarea.val(editor.getData());
      $target.val($(self.replacePicture(editor.getData())).text());
    });
    editor.on('blur', () => {
      $textarea.val(editor.getData());
      $target.val($(self.replacePicture(editor.getData())).text());
    });
    editor.on('instanceReady', function() {
      this.focus();

      $('[data-role="edit"]').one('click', function() {
        $target.val($(self.replacePicture(editor.getData())).text());
        $textarea.val(editor.getData());
        editor.destroy();
        $textarea.hide();
        $target.show();
      });
    });
  }
}

export default Essay;