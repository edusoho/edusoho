import QuestionFormBase from './form-base';

jQuery.validator.addMethod("fillCheck", function (value, element) {
    return this.optional(element) || /(\[\[(.+?)\]\])/i.test(value);
}, "请输入正确的答案,如今天是[[晴|阴|雨]]天");

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
    })
  }
}

export default Fill;