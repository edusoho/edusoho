import React, { Component } from 'react';
import MultiInput from '../../../common/widget/multi-input';
import List from './list';

//实现cheacked
//实现搜索
//实现

function initItem(dataSourceUi,data,index) {
  let obj = {
    itemId:Math.random(),
    id: data.id,
    label: data.nickname,
    isVisible: data.isVisible == 1 ? true : false,
    img: data.avatar,
    seq: index,
    outputValue: {
      id: data.id,
      isVisible: data.isVisible,
    }
  }
  dataSourceUi.push(obj);
}

function updateChecked(dataSourceUi,id) {
  dataSourceUi.map((item,index)=>{
    if(item.itemId == id) {
      dataSourceUi[index].isVisible = !dataSourceUi[index].isVisible;
      dataSourceUi[index].outputValue.isVisible = dataSourceUi[index].isVisible ? 1 : 0
    }
  })
}


export default class TeacherMultiInput extends MultiInput {
  constructor(props) {
    super(props);
    this.searchResult = null;
    console.log("dataSource");
    console.log(this.props.dataSource);
  }

  componentWillMount() {
    this.state = {
      dataSourceUi: [],
      list: [],
      outputSets: [],
    }
    this.props.dataSource.map((item,index)=>{
      initItem(this.state.dataSourceUi,item,index+1);
    })
    this.state.list = this.getList();
    this.getOutputSets();
  }

  onChecked(event) {
    let id = event.currentTarget.value;
    updateChecked(this.state.dataSourceUi,id);
    console.log({'updateChecked after': this.state.dataSourceUi});
    this.getOutputSets();
    this.setState({
      list: this.getList()
    });
  }

  addItem(data) {
    if(!this.searchResult)  {
      return;
    }
    initItem(this.state.dataSourceUi,this.searchResult,this.state.dataSourceUi.length);
    this.searchResult = null;
    this.getOutputSets();
    this.setState({
      list: this.getList()
    });
    console.log({'addItem after',this.state.dataSourceUi});
  }

  onSearch(data) {
    this.searchResult = JSON.parse(data);
  }

  getList() {
    return (<List dataSourceUi = {this.state.dataSourceUi} removeItem={(itemId)=>this.removeItem(itemId)} sortItem={(event=>this.sortItem(event))} onChecked={(event=>this.onChecked(event))}></List>);
  }
}