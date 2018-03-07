import QuestionFormBase from './form-base';

jQuery.validator.addMethod('fillCheck', function (value, element) {
  return this.optional(element) || /(\[\[(.+?)\]\])/i.test(value);
}, Translator.trans('course.question.create.fill_hint'));

class Fill extends QuestionFormBase {
  constructor($form) {
    super($form);
    this.titleEditorToolBarName = 'Question';
    this.initTitleEditor(this.validator);
    this.initAnalysisEditor();
    
    this.$titleField = $('#'+this.titleFieldId);
    this.init();
  }

  init() {
    this.$titleField.rules('add',{
      fillCheck:true
    });
  }
}

export default Fill;