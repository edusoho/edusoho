import React, { Component } from 'react';
import Option from './option';
import notify from 'common/notify';
import postal from 'postal';
import { numberConvertLetter } from '../../unit';

function InitOptionData(dataSource,props,datas,validatorDatas,seq) {
  let value = datas ? datas[props.inputValueName] : '';
  var obj = {
    optionId:`question-option-${seq}`,
    optionLabel: '选项'+ numberConvertLetter(seq),
    inputValue: value,
    checked: datas ? datas[props.checkedName] : false,
  }
  validatorDatas.Options[`question-option-${seq}`] = value.length > 0 ? 1 : 0,
  dataSource.push(obj);
}

function deleteOption(dataSource,validatorDatas,optionId) {
  for(let i = 0; i< dataSource.length ;i++) {
    if(dataSource[i].optionId==optionId) {
      dataSource.splice(i, 1);
      delete validatorDatas[optionId];
      i--;
    }else {
      dataSource[i].optionLabel = '选项'+ numberConvertLetter(i+1);
    }
  }
}

function changeOptionChecked(dataSource,validatorDatas,value,isRadio,checkedId) {
  let objValue = JSON.parse(value);
  let checkedNum = 0;
  dataSource.map((item,index)=> {
    if(item.optionId == objValue.id) {
      //如果是单选，
      if(isRadio && objValue.checked){
        return;
      }
      console.log(isRadio);
      dataSource[index].checked= !objValue.checked;
    }else if(isRadio && !objValue.checked){
      //如果是单选;
      dataSource[index].checked = false;
    }
    if(dataSource[index].checked) {
      checkedNum++;
    }
  });
  validatorDatas.checkedNum = checkedNum;
  console.log(validatorDatas);
  console.log(dataSource);
}

export default class QuestionOptions extends Component {
  constructor(props) {
    super(props);
    this.state = {
      dataSource:[],
      isValidator: false,
    }

    //验证的数据
    this.validatorDatas = {
      checkedNum: 0,
      Options: {}
    };

    const dataSource = this.props.dataSource;
    if(dataSource.length > 0) {
      dataSource.map((item,index)=>{
        InitOptionData(this.state.dataSource,this.props,item,this.validatorDatas,index);
      })
    }else {
      for(let i = 1; i<= this.props.defaultNum;i++) {
        InitOptionData(this.state.dataSource,this.props,null,this.validatorDatas,i);
      }
    }
    this.subscriptionMessage();
  }

  subscriptionMessage() {
    postal.subscribe({
      channel  : "manage-question",
      topic    : "question-create-form-validator-start",
      callback : (data, envelope) =>{
        this.validatorOptions(data);
      }
    });
  }

  publishMessage(isValidator) {
    console.log({'publishMessage':isValidator});
    postal.publish({
      channel : "manage-question",
      topic : "question-create-form-validator-end",
      data : {
        isValidator: isValidator,
      }
    });
  }

  validatorOptions(data) {
    console.log('validatorOptions');
    let validNum = 0;

    //触发视觉
    this.setState({
      isValidator: data.isValidator,
    })

    for(let option in this.validatorDatas.Options){
      validNum += this.validatorDatas.Options[option];
    }

    console.log(this.state.dataSource.length);
    console.log(validNum);

    if(validNum < this.state.dataSource.length ) {

      return;
    }

    if(this.validatorDatas.checkedNum < this.props.minCheckedNum ) {
      notify('danger','请选择正确答案!');
    }else {
      console.log('publishMessage');
      this.publishMessage(true);
    }
  }

  addOption() {
    if(this.state.dataSource.length >= this.props.maxNum) {
      notify('danger', `选项最多${this.props.maxNum}个!`);
      return;
    }
    InitOptionData(this.state.dataSource,this.props,null,this.validatorDatas,this.state.dataSource.length+1);
    this.setState({
      dataSource:this.state.dataSource,
    });
    console.log({'dataSource':this.state.dataSource});
  }

  changeOptionChecked(value) {
    changeOptionChecked(this.state.dataSource,this.validatorDatas,value,this.props.isRadio);
    this.setState({
      dataSource:this.state.dataSource,
    });
    if(this.validatorDatas.checkedNum <= 0) {
      this.publishMessage(false);
    }
  }

  deleteOption(id) {
    if(this.state.dataSource.length <= this.props.minNum) {
      notify('danger', `选项最少${this.props.maxNum}个!`);
      return;
    }
    deleteOption(this.state.dataSource,this.validatorDatas,id);
    this.setState({
      dataSource:this.state.dataSource,
    });
    console.log(this.validatorDatas);
  }

  render() {
    return(
      <div className="question-options-group">
        {
          this.state.dataSource.map((item,index)=>{
            return (
              <Option isRadio = {this.props.isRadio} publishMessage= {(isValidator)=>this.publishMessage(isValidator)} validatorDatas = {this.validatorDatas} isValidator= {this.state.isValidator} datas = {item} key = {index} index = {index} deleteOption ={(id)=>this.deleteOption(id)} changeOptionChecked= {(id)=>this.changeOptionChecked(id)}></Option>
            )
          })
        }
        <div className="form-group">
          <div className="col-md-8 col-md-offset-2">
            <a className="btn btn-success btn-sm pull-right" onClick={()=>this.addOption()}>新增选项</a>
          </div>
        </div>
      </div>
    )
  }
}


QuestionOptions.defaultProps = {
  defaultNum: 4, //默认选项个数
  maxNum: 10,//最多选项的个数
  minNum: 2,//最少选项的个数
  isRadio: false,//是否为单选
  minCheckedNum:1,//至少选择几个答案
  inputValueName: 'value', //值的字段名
  idName: 'id',//id的字段名
  checkedName:'checked',//答案的字段名
}

