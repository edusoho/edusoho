import QuestionChoice from './question-choice';
import ReactDOM from 'react-dom';
import React from 'react';
import QuestionOptions from '../../../common/widget/question-options';

class SingleChoice extends QuestionChoice {
  initOptions() {
    ReactDOM.render( <QuestionOptions dataSource={[]} inputValueName='value' checkedName="checked" idName="id" />,
      document.getElementById('question-options')
    );
  }
}

export default SingleChoice;