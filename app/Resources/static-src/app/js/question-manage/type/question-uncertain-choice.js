import QuestionFormBase from './form-base';

class UncertainChoice extends QuestionFormBase {
  constructor($form) {
    super($form);

    this.initTitleEditor();
    this.initAnalysisEditor();
    this.initOptions();
  }
  initOptions() {
    ReactDOM.render( <QuestionOptions dataSource={[]} inputValueName='value' checkedName="checked" idName="id"/>,
      document.getElementById('question-options')
    );
  }
}

export default UncertainChoice;