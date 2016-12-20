import QuestionFormBase from './form-base';
import ReactDOM from 'react-dom';
import React from 'react';
import QuestionOptions from '../../../common/widget/question-options';

class Choice extends QuestionFormBase {
  constructor($form) {
    super($form);
    this.initTitleEditor();
    this.initAnalysisEditor();
  }
}

export default Choice;