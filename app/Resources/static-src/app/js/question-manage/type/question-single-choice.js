import QuestionChoice from './question-choice';
import ReactDOM from 'react-dom';
import React from 'react';
import QuestionOptions from '../../../common/component/question-options';

class SingleChoice extends QuestionChoice {
  initOptions() {
    let dataSource = $('#question-options').data('choices');
    let dataAnswer = $('#question-options').data('answer');
    if(dataSource) {
      dataSource = JSON.parse(dataSource);
      dataAnswer = JSON.parse(dataAnswer);
    }else {
      dataSource= [];
    }

    let url = $('#question-options').data('image-upload-url');

    ReactDOM.render( <QuestionOptions filebrowserImageUploadUrl= {url} dataSource={dataSource} dataAnswer={dataAnswer}  isRadio={true}/>,
      document.getElementById('question-options')
    );
  }
}

export default SingleChoice;