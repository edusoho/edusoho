import AttachmentActions from '../../attachment/widget/attachment-actions';

class BaseQuestion {
  constructor($form, object) {
    this.$form = $form;
    this.operate = object;
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
    let self = this;

    if (self.validator.form()) {
      $(event.currentTarget).button('loading');
      let question = self.getQuestion();
      self.finishEdit(question);
    }
  }

  finishEdit(question) {
    let self = this;
    let token = $('.js-hidden-token').val();
    $.each(question, function(name, value){
      self.operate.updateQuestionItem(token, name, value);
    });
    question = self.operate.getQuestion(token);
    let seq = self.operate.getQuestionOrder(token);
    $.get(self.$form.data('url'), {seq: seq, question: question, token: token}, html=> {
      self.$form.parent('.subject-item').replaceWith(html);
    });
  }

  getQuestion() {
    let question = {};

    $('*[data-edit]').each(function(){
      let name = $(this).data('edit');
      let value = $(this).val();
      question[name] = value;
    });
    question['difficulty'] = $('input[name=\'difficulty\']:checked').val();
    question = this.filterQuestion(question);

    return question;
  }

  filterQuestion(question) {
    return question;
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
        score: {
          required: true,
          number: true,
          max: 999,
          min: 0,
          es_score: true
        }
      },
      messages: {
        difficulty: Translator.trans('course.question.create.difficulty_required_error_hint'),
        stem: {required: Translator.trans('请输入题干')}
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
        $target.val(editor.getData());
        editor.destroy();
        $target.hide();
        $input.show();
      });
    });
  }

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