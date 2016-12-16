import QuestionFormBase from './form-base';

class UncertainChoice extends QuestionFormBase {
  constructor($form) {
    super($form);

    this.initTitleEditor();
    this.initAnalysisEditor();
  }
}

export default UncertainChoice;