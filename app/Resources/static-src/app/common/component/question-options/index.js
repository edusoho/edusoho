import React, { Component } from 'react';
import Option from './option';
import notify from 'common/notify';
import postal from 'postal';
import { numberConvertLetter } from '../../unit';

function InitOptionData(dataSource,inputValue,validatorDatas,seq,checked) {
  var obj = {
    optionId:Math.random().toString().replace('.',''),
    optionLabel: Translator.trans('activity.testpaper_manage.question_options')+ numberConvertLetter(seq),
    inputValue: inputValue,
    checked: checked,
  };
  validatorDatas.Options[obj.optionId] = inputValue.length > 0 ? 1 : 0;
  if(checked) {
    validatorDatas.checkedNum += 1;
  }
  dataSource.push(obj);
}

function deleteOption(dataSource,validatorDatas,optionId) {
  for(let i = 0; i< dataSource.length ;i++) {
    if(dataSource[i].optionId==optionId) {
      if(dataSource[i].checked) {
        validatorDatas.checkedNum = 0;
      }
      dataSource.splice(i, 1);
      console.log(validatorDatas.Options[optionId]);
      console.log(dataSource);
      delete validatorDatas.Options[optionId];
      i--;
    }else {
      dataSource[i].optionLabel = Translator.trans('activity.testpaper_manage.question_options')+ numberConvertLetter(i+1);
    }
  }
}

function changeOptionChecked(dataSource,validatorDatas,id,checked,isRadio) {
  let checkedNum = 0;
  dataSource.map((item,index)=> {
    if(!isRadio) {
      if(item.optionId == id ) {
        dataSource[index].checked= !checked;
      }
    }else {
      //单选
      if(item.optionId == id && !checked ) { 
        dataSource[index].checked  = true;
      }else if(!checked){
        dataSource[index].checked = false;
      }
    }
    //计算选择的答案
    console.log(dataSource[index].checked);
    if(dataSource[index].checked) {
      checkedNum++;
    }
  });
  console.log(checkedNum);
  validatorDatas.checkedNum = checkedNum;
}

function updateOption(dataSource,validatorDatas,id,value) {
  dataSource.map((item,index)=>{
    if(item.optionId == id) {
      dataSource[index].inputValue = value;
    }
  });
}

export default class QuestionOptions extends Component {
  constructor(props) {
    super(props);
    this.state = {
      dataSource:[],
      isValidator: false,
    };
    //验证的数据
    this.validatorDatas = {
      checkedNum: 0,
      Options: {}
    };
    const dataSource = this.props.dataSource;
    const dataAnswer = this.props.dataAnswer;
    if(dataSource.length > 0) {
      dataSource.map((item,index)=>{
        let checked = false;
        for( let i = 0 ;i< dataAnswer.length;i++) {
          if(index == dataAnswer[i]) {
            checked = true;
          }
        }
        InitOptionData(this.state.dataSource,item,this.validatorDatas,index+1,checked);
      });
    }else {
      for(let i = 1; i <= this.props.defaultNum;i++) {
        InitOptionData(this.state.dataSource,'',this.validatorDatas,i,false);
      }
    }
    this.subscriptionMessage();
    console.log(this.validatorOptions);
  }

  subscriptionMessage() {
    postal.subscribe({
      channel  : 'manage-question',
      topic    : 'question-create-form-validator-start',
      callback : (data, envelope) =>{
        this.validatorOptions(data);
      }
    });
  }

  publishMessage(isValidator) {
    postal.publish({
      channel : 'manage-question',
      topic : 'question-create-form-validator-end',
      data : {
        isValidator: isValidator,
      }
    });
  }

  validatorOptions(data) {
    let validNum = 0;

    //触发视觉
    this.setState({
      isValidator: data.isValidator,
    });

    for(let option in this.validatorDatas.Options){
      validNum += this.validatorDatas.Options[option];
    }

    if(validNum < this.state.dataSource.length ) {
      console.log(' validNum is error ');
      return;
    }

    if(this.validatorDatas.checkedNum < this.props.minCheckedNum ) {
      notify('danger',Translator.trans('course.question.create.choose_min_answer_num_hint', {'minCheckedNum' : this.props.minCheckedNum}));
    }else {
      console.log('publishMessage');
      this.publishMessage(true);
    }
  }

  addOption() {
    if(this.state.dataSource.length >= this.props.maxNum) {
      notify('danger', Translator.trans('course.question.create.choose_max_num_hint', {'maxNum' : this.props.maxNum}));
      return;
    }
    InitOptionData(this.state.dataSource,'',this.validatorDatas,this.state.dataSource.length+1,false);
    this.setState({
      dataSource:this.state.dataSource,
    });
    console.log({'dataSource':this.state.dataSource});
    console.log({'validatorDatas':this.validatorDatas});
  }

  changeOptionChecked(id,checked) {
    changeOptionChecked(this.state.dataSource,this.validatorDatas,id,checked,this.props.isRadio);
    this.setState({
      dataSource:this.state.dataSource,
    });
    if(this.validatorDatas.checkedNum <= 0) {
      this.publishMessage(false);
    }
  }

  deleteOption(id) {
    if(this.state.dataSource.length <= this.props.minNum) {
      notify('danger', Translator.trans('course.question.create.choose_min_num_hint', {'minNum' : this.props.minNum}));
      return;
    }
    deleteOption(this.state.dataSource,this.validatorDatas,id);
    this.setState({
      dataSource:this.state.dataSource,
    });
    console.log({'validatorDatas':this.validatorDatas});
  }

  updateInputValue(id,value) {
    updateOption(this.state.dataSource,this.validatorDatas,id,value);
    this.validatorDatas.Options[id] = value.length > 0 ? 1:0;
    if(value.length <=0 ) {
      this.publishMessage(false);
    }
    this.setState({
      dataSource: this.state.dataSource,
    });

    console.log(this.state.dataSource);
  }

  render() {
    let createNewName = Translator.trans('course.question.create_hint');
    return(
      <div className="question-options-group">
        {
          this.state.dataSource.map((item,index)=>{
            return (
              <Option imageUploadUrl= {this.props.imageUploadUrl} imageDownloadUrl={this.props.imageDownloadUrl}  isRadio = {this.props.isRadio} publishMessage= {(isValidator)=>this.publishMessage(isValidator)} validatorDatas = {this.validatorDatas} isValidator= {this.state.isValidator} datas = {item} key = {index} index = {index} deleteOption ={(id)=>this.deleteOption(id)} changeOptionChecked= {(id,checked)=>this.changeOptionChecked(id,checked)} updateInputValue={ (id,value)=>this.updateInputValue(id,value)}></Option>
            );
          })
        }
        <div className="form-group">
          <div className="col-md-8 col-md-offset-2">
            <a className="cd-btn cd-btn-success cd-btn-sm pull-right" onClick={()=>this.addOption()}>{ createNewName }</a>
          </div>
        </div>
      </div>
    );
  }
}

QuestionOptions.defaultProps = {
  defaultNum: 4, //默认选项个数
  maxNum: 10,//最多选项的个数
  minNum: 2,//最少选项的个数
  isRadio: false,//是否为单选
  minCheckedNum:1,//至少选择几个答案
};