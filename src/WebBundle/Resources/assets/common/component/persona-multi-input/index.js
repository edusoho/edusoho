import React, { Component } from 'react';
import MultiInput from '../../../common/widget/multi-input';
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
    console.log({'dataSource':this.props.dataSource});
  }

  componentWillMount() {
    this.state = {
      dataSourceUi: [],
      list: [],
      outputSets: [],
    }
    this.props.dataSource.map((item,index)=>{
      initItem(this.state.dataSourceUi,item,index+1,this.props);
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
    //@TODO重复添加提示
    initItem(this.state.dataSourceUi,this.searchResult,this.state.dataSourceUi.length+1,this.props);
    this.searchResult = null;
    this.getOutputSets();
    this.setState({
      list: this.getList()
    });
    console.log({'addItem after':this.state.dataSourceUi});
  }

  onSearch(data) {
    this.searchResult = JSON.parse(data);
  }

  getList() {
    console.log(this.props.sortable);
    return (<List listClassName={this.props.listClassName}  dataSourceUi = {this.state.dataSourceUi} removeItem={(itemId)=>this.removeItem(itemId)} sortItem={(event=>this.sortItem(event))} onChecked={(event=>this.onChecked(event))} sortable={this.props.sortable}></List>);
  }
}

// @TODO,能自动继承父组件的默认参数，并且新增自己的默认参数
MultiInput.propTypes = {
  multiInputClassName: React.PropTypes.string,
  listClassName: React.PropTypes.string,
  dataSource: React.PropTypes.array.isRequired,
  sortable: React.PropTypes.bool, 
  addable:React.PropTypes.bool,
  searchable: React.PropTypes.shape({
    enable: React.PropTypes.bool,
    url: React.PropTypes.string,
  }),
  id:React.PropTypes.string,
  nickname:React.PropTypes.string,
  avatar: React.PropTypes.string,
  isVisible:React.PropTypes.string,
};

MultiInput.defaultProps = {
  multiInputClassName:'multi-group',
  listClassName:'',
  dataSource: [],//必须是数组
  sortable: true,
  addable: true,
  searchable: {
    enable: false,
    url: '',
  },//必须是bool
  outputDataElement:'hidden-input',//必须是string,
  id: 'id',
  nickname:'nickname',
  avatar: 'avatar',
  isVisible:'isVisible',
};
