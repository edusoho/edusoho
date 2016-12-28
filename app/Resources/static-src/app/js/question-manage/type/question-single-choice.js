import QuestionChoice from './question-choice';
import ReactDOM from 'react-dom';
import React from 'react';
import QuestionOptions from '../../../common/component/question-options';

class SingleChoice extends QuestionChoice {
  initOptions() {
    ReactDOM.render( <QuestionOptions dataSource={[]} inputValueName='value' checkedName="checked" idName="id" isRadio={true}/>,
      document.getElementById('question-options')
    );
  }
}

export default SingleChoice;