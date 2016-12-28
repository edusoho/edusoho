import QuestionFormBase from './form-base';
import ReactDOM from 'react-dom';
import React from 'react';
import QuestionOptions from '../../../common/widget/question-options';
import postal from 'postal';


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

class Choice extends QuestionFormBase {
  constructor($form) {
    super($form);
    this.isSubmit =  false;
    this.$submit = null;
    this.initTitleEditor();
    this.initAnalysisEditor();
    this.initOptions();
    this.subscriptionMessage();
  }

  _initEvent() {
    this.$form.on('click','[data-role=submit]',event=>this.submitForm(event));
  }

  submitForm(event) {
    this.$submit = $(event.currentTarget);
    
    if(this.validator.form() && this.isSubmit ) {
      this.submit();
    }
    if(!this.isSubmit ) {
      this.publishMessage();
    }
  }

  submit() {
    this.$submit.button('loading');
    this.$form.submit();
  }

  initOptions() {
    ReactDOM.render( <QuestionOptions dataSource={[]} inputValueName='value' checkedName="checked" idName="id" minCheckedNum={ 2 } />,
      document.getElementById('question-options')
    );
  }

  publishMessage() {
    postal.publish({
      channel : "manage-question",
      topic : "question-create-form-validator-start",
      data : {
        isValidator: true,
      }
    });
  }

  subscriptionMessage() {
    postal.subscribe({
      channel  : "manage-question",
      topic    : "question-create-form-validator-end",
      callback : (data, envelope) =>{
        this.isSubmit = data.isValidator;
        console.log({
          'subscriptionMessage':this.isSubmit
        });
        if(this.isSubmit &&  this.validator.form()) {
          console.log('submit by subscriptionMessage');
          this.submit();
        }
      }
    });
  }
}

export default Choice;




// import QuestionFormBase from './form-base';
// import ReactDOM from 'react-dom';
// import React from 'react';
// import QuestionOptions from '../../../common/widget/question-options';

// class Choice extends QuestionFormBase {
//   constructor($form) {
//     super($form);
//     this.initTitleEditor();
//     this.initAnalysisEditor();
//     this.initOptions();
//   }
//   initOptions() {
//     ReactDOM.render( <QuestionOptions dataSource={[]} inputValueName='value' checkedName="checked" idName="id"/>,
//       document.getElementById('question-options')
//     );
//   }
// }

// export default Choice;