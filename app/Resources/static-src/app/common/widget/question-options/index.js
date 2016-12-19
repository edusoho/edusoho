import React, { Component } from 'react';
import Option from './option';

//id ,和字，和答案。
function InitOptionData(dataSourceUi,datas,seq) {
  var obj = {
    optionId:`question-option-${seq}`,
    optionLabel: '选项'+ seq,
    optionValue: datas ? datas.value : '',
    checked: datas ? datas.checked : false,
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

function changeOption(dataSourceUi,value,isRadio) {
  dataSourceUi.map((item,index)=> {

    if(item.optionId == value.id) {
      dataSourceUi[index].checked= !value.checked;
    }else if(isRadio && dataSourceUi[index].checked){

    }
  })
  console.log(dataSourceUi);
}

export default class QuestionOptions extends Component {
  constructor(props) {
    super(props);
    this.state = {
      dataSourceUi:[]
    }
    console.log(this.props.dataSource);
    const dataSource = this.props.dataSource;
    if(dataSource.length > 0) {
      dataSource.map((item,index)=>{
        InitOptionData(this.state.dataSourceUi,item,);
      })
    }else {
      for(let i = 1; i<= this.props.defaultNum;i++) {
        InitOptionData(this.state.dataSourceUi,null,i);
      }
    }
  }

  addOption() {
    if(this.state.dataSourceUi.length >= this.props.maxNum) {
      console.log(`不大于${this.props.maxNum}`);
      return;
    }
    InitOptionData(this.state.dataSourceUi,null,this.state.dataSourceUi.length+1);
    this.setState({
      dataSourceUi:this.state.dataSourceUi,
    });
  }

  changeOption(event) {
    changeOption(this.state.dataSourceUi,event.currentTarget.value,this.props.isRadio);
    this.setState({
      dataSourceUi:this.state.dataSourceUi,
    });
  }

  deleteOption(event) {
    if(this.state.dataSourceUi.length <= this.props.minNum) {
      console.log(`不少于${this.props.minNum}`);
      return;
    }
    deleteOption(this.state.dataSourceUi,event.currentTarget.id);
    this.setState({
      dataSourceUi:this.state.dataSourceUi,
    });
  }

  render() {
    return(
      <div className="question-options-group">
        <Option dataSourceUi = {this.state.dataSourceUi} deleteOption ={(event)=>this.deleteOption(event)} changeOption= {(event)=>this.changeOption(event)}></Option>
        <div className="form-group">
          <div className="col-md-8 col-md-offset-2">
            <button className="btn btn-success btn-sm pull-right" onClick={()=>this.addOption()}>新增选项</button>
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
  isRadio: true,
}

