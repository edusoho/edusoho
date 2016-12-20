import React, { Component } from 'react';
import Option from './option';

//id ,和字，和答案。
function InitOptionData(dataSourceUi,props,datas,seq) {
  var obj = {
    optionId:`question-option-${seq}`,
    optionLabel: '选项'+ seq,
    inputValue:datas ? datas[props.inputValueName] : '',
    checked: datas ? datas[props.checkedName] : false,
    editor: null,
  }

  dataSourceUi.push(obj);
  console.log(dataSourceUi);
}

function deleteOption(dataSourceUi,optionId) {
  for(let i = 0; i< dataSourceUi.length ;i++) {
    if(dataSourceUi[i].optionId==optionId) {
      dataSourceUi.splice(i, 1);
      i--;
    }else {
      dataSourceUi[i].optionLabel = '选项'+ (i+1);
    }
  }
  console.log(dataSourceUi);
}

function changeOption(dataSourceUi,value,isRadio,checkedId) {
  let objValue = JSON.parse(value);
  dataSourceUi.map((item,index)=> {
    console.log(objValue.id);
    console.log(item.optionId);
    if(item.optionId == objValue.id) {
      if(isRadio && objValue.checked){
        return;
      }
      dataSourceUi[index].checked= !objValue.checked;
    }else if(isRadio && !objValue.checked){
      dataSourceUi[index].checked = false;
    }
  })
  console.log(dataSourceUi);
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
      console.log(`不大于${this.props.maxNum}`);
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
      console.log(`不少于${this.props.minNum}`);
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
              <Option item = {item} key = {index} deleteOption ={(id)=>this.deleteOption(id)} changeOption= {(id)=>this.changeOption(id)} updateInputValue = {(id,inputValue)=>this.updateInputValue(id,inputValue)}></Option>
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

