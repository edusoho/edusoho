import QuestionFormBase from './form-base';
import ReactDOM from 'react-dom';
import React from 'react';
import QuestionOptions from '../../../common/widget/question-options';

// let dataSource = [{
//   id: 'question-option-1',//是否需要保存ID，
//   checked: 0,//选项是否为正确答案；
//   value:"sdfsdf "//选项的value 
// },
// {
//   id: 'question-option-2',//是否需要保存ID，
//   checked: 0,//选项是否为正确答案；
//   value:"d123d"//选项的value 
// },
// {
//   id: 'question-option-3',//是否需要保存ID，
//   checked: 0,//选项是否为正确答案；
//   value:"d4234d"//选项的value 
// },
// {
//   id: 'question-option-4',//是否需要保存ID，
//   checked: 0,//选项是否为正确答案；
//   value:"ddg675"//选项的value 
// },
// ];

class SingleChoice extends QuestionFormBase {
  constructor($form) {
    super($form);
    this.initTitleEditor();
    this.initAnalysisEditor();
    this.initOptions();
  }

  initOptions() {
    ReactDOM.render( <QuestionOptions dataSource={[]} inputValueName='value' checkedName="checked" idName="id" isRadio={true}/>,
      document.getElementById('question-options')
    );
  }
}

export default SingleChoice;