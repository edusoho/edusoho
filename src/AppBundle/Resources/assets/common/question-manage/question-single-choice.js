import QuestionFormBase from './form-base';

class SingleChoice extends QuestionFormBase {
  constructor($form) {
    super($form);
    
    this.initTitleEditor();
    this.initAnalysisEditor();
  }
}

export default SingleChoice;