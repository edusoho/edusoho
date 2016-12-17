import React, { Component } from 'react';
import Option from './option';

function InitOptionData(dataSource,datas,seq) {
  var obj = {
    optionId:Math.random(),
    optionLabel: '',
    optionValue:'',
    checked: '',
  }
  dataSourceUi.push(obj);
}



export default class QuestionOptions extends Component {
  constructor(props) {
    super(props);
    this.state = {
      dataSourceUi:[]
    }
    const dataSource = this.props.dataSource;
    if(dataSource.length > 0) {
      dataSource.map((item,index)=>{
        InitOptionData(dataSourceUi,item,);
      })
    }else {
      for(let i = 1; i<= this.props.defaultNum;i++) {
        InitOptionData(dataSourceUi,null,i);
      }
    }
  }


  addOption() {

  }

  deleteOption() {

  }

  render() {
    return(
      <Option dataSourceUi = {this.state.dataSourceUi}></Option>
    )
  }
}


QuestionOptions.defaultProps = {
  defaultNum: 4,
  maxNum: 10,
  minNum: 2,
}

