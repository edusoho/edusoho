import QuestionFormBase from './form-base';

class Material extends QuestionFormBase {
  constructor($form) {
    super($form);
    
    this.initTitleEditor();
    this.initAnalysisEditor();
  }
}

export default Material;