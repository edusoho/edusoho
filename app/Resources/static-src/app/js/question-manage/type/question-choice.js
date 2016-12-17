import QuestionFormBase from './form-base';
import ReactDOM from 'react-dom';
import React from 'react';
import QuestionOptions from '../../../common/widget/question-options';

class Choice extends QuestionFormBase {
  constructor($form) {
    super($form);
    this.initTitleEditor();
    this.initAnalysisEditor();
    this.initTemple();
  }

  initTemple() {
    let id = 'choices-group';
    ReactDOM.render( <QuestionOptions />,
      document.getElementById(id),
    );
  }
}

export default Choice;