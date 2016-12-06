import QuestionFormBase from './form-base';

class Choice extends QuestionFormBase {
  constructor($form) {
    super($form);

    this.initTitleEditor();
    this.initAnalysisEditor();
  }
}

export default Choice;