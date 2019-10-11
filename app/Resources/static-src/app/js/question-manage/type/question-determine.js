import QuestionFormBase from './form-base';

class Datermine extends QuestionFormBase {
  constructor($form) {
    super($form);

    this.initTitleEditor(this.validator);
    this.initAnalysisEditor();

    this.$answerField = $('[name="answer[]"]');

    this.init();
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

  init() {
    this.$answerField.rules('add', {
      required: true,
      messages: {
        required: Translator.trans('course.question.create.right_answer_required_error_hint')
      }
    });

  }
}

export default Datermine;