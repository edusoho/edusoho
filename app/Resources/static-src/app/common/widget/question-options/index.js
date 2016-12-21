import React, { Component } from 'react';
import Option from './option';
import notify from 'common/notify';

function convert(num){
  return num <= 26 ? String.fromCharCode(num + 64) : convert(~~((num - 1) / 26)) + convert(num % 26 || 26);
}

function InitOptionData(dataSource,props,datas,validatorDatas,seq) {
  var obj = {
    optionId:`question-option-${seq}`,
    optionLabel: '选项'+ convert(seq),
    inputValue:datas ? datas[props.inputValueName] : '',
    checked: datas ? datas[props.checkedName] : false,
  }
  validatorDatas[`question-option-${seq}`] = datas ? datas[props.inputValueName] : '',
  dataSource.push(obj);
}

function deleteOption(dataSource,validatorDatas,optionId) {
  for(let i = 0; i< dataSource.length ;i++) {
    if(dataSource[i].optionId==optionId) {
      dataSource.splice(i, 1);
      delete validatorDatas[optionId];
      i--;
    }else {
      dataSource[i].optionLabel = '选项'+ convert(i+1);
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
      validNum:0,
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
  }

  sub() {
    //首先出发验证，
    
    //验证是否选择了合理个数的答案；
    //验证每一项目是否为空；
    //是否需要时候去显示验证效果；
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
  }

  changeOptionChecked(value) {
    changeOptionChecked(this.state.dataSource,this.validatorDatas,value,this.props.isRadio);
    this.setState({
      dataSource:this.state.dataSource,
    });
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
              <Option isRadio = {this.props.isRadio} isValidator= {this.state.isValidator} datas = {item} key = {index} deleteOption ={(id)=>this.deleteOption(id)} changeOptionChecked= {(id)=>this.changeOptionChecked(id)}></Option>
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
  defaultNum: 4, 
  maxNum: 10,
  minNum: 2,
  isRadio: false,//是否为单选
  inputValueName: 'value',
  idName: 'id',
  checkedName:'checked',
}

