import QuestionFormBase from './form-base';

class Datermine extends QuestionFormBase {
  constructor($form) {
    super($form);

    this.initTitleEditor();
    this.initAnalysisEditor();

    this.$answerField = $('[name="answer\[\]"]');

    this.init();
  }

  init() {
    this.$answerField.rules('add',{
      required:true,
      messages:{
        required:"请选择正确答案"
      }
    })
    
  }
}

export default Datermine;