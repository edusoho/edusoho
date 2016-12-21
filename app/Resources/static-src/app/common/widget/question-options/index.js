import React, { Component } from 'react';
import Option from './option';
import notify from 'common/notify'


function convert(num){
  return num <= 26 ? String.fromCharCode(num + 64) : convert(~~((num - 1) / 26)) + convert(num % 26 || 26);
}

function InitOptionData(dataSourceUi,props,datas,seq) {
  var obj = {
    optionId:`question-option-${seq}`,
    optionLabel: '选项'+ convert(seq),
    inputValue:datas ? datas[props.inputValueName] : '',
    checked: datas ? datas[props.checkedName] : false,
    editor: null,
  }
  dataSourceUi.push(obj);
}

function deleteOption(dataSourceUi,optionId) {
  for(let i = 0; i< dataSourceUi.length ;i++) {
    if(dataSourceUi[i].optionId==optionId) {
      dataSourceUi.splice(i, 1);
      i--;
    }else {
      dataSourceUi[i].optionLabel = '选项'+ convert(i+1);
    }
  }
}

function changeOption(dataSourceUi,value,isRadio,checkedId) {
  let objValue = JSON.parse(value);
  dataSourceUi.map((item,index)=> {
    if(item.optionId == objValue.id) {
      //如果是单选，
      if(isRadio && objValue.checked){
        return;
      }
      dataSourceUi[index].checked= !objValue.checked;
    }else if(isRadio && !objValue.checked){
      //如果是单选;
      dataSourceUi[index].checked = false;
    }
  })
}

export default class QuestionOptions extends Component {
  constructor(props) {
    super(props);
    this.state = {
      dataSourceUi:[],
    }
    const dataSource = this.props.dataSource;
    if(dataSource.length > 0) {
      dataSource.map((item,index)=>{
        InitOptionData(this.state.dataSourceUi,this.props,item,index);
      })
    }else {
      for(let i = 1; i<= this.props.defaultNum;i++) {
        InitOptionData(this.state.dataSourceUi,this.props,null,i);
      }
    }
  }

  componentDidMount() {
    console.log('componentDidMount');
    $("[name='answers']").rules("add", { required: true, messages: { required: "未选择正确答案"} });
  }

  addOption() {
    if(this.state.dataSourceUi.length >= this.props.maxNum) {
      notify('danger', `选项最多${this.props.maxNum}个!`);
      return;
    }
    InitOptionData(this.state.dataSourceUi,this.props,null,this.state.dataSourceUi.length+1);
    this.setState({
      dataSourceUi:this.state.dataSourceUi,
    });
  }

  changeOption(value) {
    changeOption(this.state.dataSourceUi,value,this.props.isRadio);
    this.setState({
      dataSourceUi:this.state.dataSourceUi,
    });
  }

  deleteOption(id) {
    if(this.state.dataSourceUi.length <= this.props.minNum) {
      notify('danger', `选项最少${this.props.maxNum}个!`);
      return;
    }
    deleteOption(this.state.dataSourceUi,id);
    this.setState({
      dataSourceUi:this.state.dataSourceUi,
    });
  }

  updateInputValue(id,inputValue) {
    this.state.dataSourceUi.map((item,index)=>{
      if(item.optionId == id) {
        console.log('ok');
        item.inputValue = inputValue;
      }
    });
    this.setState({
      dataSourceUi:this.state.dataSourceUi,
    });
    console.log(this.state.dataSourceUi);
  }

  render() {
    let outputSets= [];
    let checkedLenght = '';
    this.state.dataSourceUi.map((item,index)=>{
      let obj = {
        [this.props.idName]:item.optionId,
        [this.props.inputValueName]:item.inputValue,
        [this.props.checkedName]:item.checked ? 1 : 0,
      }
      if(item.checked ) {
        checkedLenght = item.optionId;
      }
      outputSets.push(obj);   
    });

    return(
      <div className="question-options-group">
        {
          this.state.dataSourceUi.map((item,index)=>{
            return (
              <Option isRadio = {this.props.isRadio} item = {item} key = {index} deleteOption ={(id)=>this.deleteOption(id)} changeOption= {(id)=>this.changeOption(id)} updateInputValue = {(id,inputValue)=>this.updateInputValue(id,inputValue)}></Option>
            )
          })
        }
        <div className="form-group">
          <div className="col-md-8 col-md-offset-2">
            <a className="btn btn-success btn-sm pull-right" onClick={()=>this.addOption()}>新增选项</a>
            <input type="hidden" value={checkedLenght} name="answers"/>
          </div>
        </div>
        <input type="hidden" value={JSON.stringify(outputSets)} />
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

