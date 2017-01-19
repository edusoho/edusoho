import React, { Component } from 'react';
import MultiInput from '../multi-input';
import List from './list';

function initItem(dataSourceUi,data,index,props) {
  let obj = {
    itemId:Math.random(),
    nickname: data[props.nickname],
    isVisible: data[props.isVisible] == 1 ? true : false,
    avatar: data[props.avatar],
    seq: index,
    outputValue: {
      [props.id]: data[props.id],
      [props.isVisible]: data[props.isVisible] ,
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

export default class PersonaMultiInput extends MultiInput {
  constructor(props) {
    super(props);
    this.searchResult = null;
  }

  componentWillMount() {
    this.state = {
      dataSourceUi: [],
    }
    this.props.dataSource.map((item,index)=>{
      initItem(this.state.dataSourceUi,item,index+1,this.props);
    })
  }

  onChecked(event) {
    let id = event.currentTarget.value;
    updateChecked(this.state.dataSourceUi,id);
    this.setState({
      dataSourceUi: this.state.dataSourceUi,
    });
  }

  addItem(value,data) {
    console.log('addItem');
    console.log(data);
    if(!this.searchResult)  {
      return;
    }
    //@TODO重复添加提示
    initItem(this.state.dataSourceUi,data,this.state.dataSourceUi.length+1,this.props);
    this.setState({
      dataSourceUi: this.state.dataSourceUi,
    });
    console.log({'addItem after':this.state.dataSourceUi});
  }

  getList() {
    console.log('new getList');
    console.log(this.props.sortable);
    return (<List listClassName={this.props.listClassName}  dataSourceUi = {this.state.dataSourceUi}  sortable={this.props.sortable}></List>);
  }
}

PersonaMultiInput.propTypes = {
  ...MultiInput.propTypes,
  id:React.PropTypes.string,
  nickname:React.PropTypes.string,
  avatar: React.PropTypes.string,
  isVisible:React.PropTypes.string,
};

PersonaMultiInput.defaultProps = {
  ...MultiInput.defaultProps,
  id: 'id',
  nickname:'nickname',
  avatar: 'avatar',
  isVisible:'isVisible',
};

