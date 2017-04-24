import QuestionFormBase from './form-base';

class Datermine extends QuestionFormBase {
  constructor($form) {
    super($form);

    this.initTitleEditor(this.validator);
    this.initAnalysisEditor();

    this.$answerField = $('[name="answer\[\]"]');

    this.init();
  }

  init() {
    this.$answerField.rules('add',{
      required:true,
      messages:{
        required:Translator.trans('请输入正确答案')
      }
    })
    
  }
}

export default Datermine;