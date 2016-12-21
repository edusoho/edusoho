import QuestionFormBase from './form-base';
import ReactDOM from 'react-dom';
import React from 'react';
import QuestionOptions from '../../../common/widget/question-options';

class Choice extends QuestionFormBase {
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

export default Choice;