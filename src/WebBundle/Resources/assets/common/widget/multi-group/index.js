import React, { Component } from 'react';
import List from './list';
import InputGroup from './input-group';
import './style.less'



//items 数据列表，1、isSor是否拖动排序，输入时是否可搜索（true,false）,是否选中
//list 数据为什么要放到父组件本身呢》
//组件为甚要关心你要什么数据；
//

function isType(type) {
  return function(obj) {
    return {}.toString.call(obj) == "[object " + type + "]"
  }
}

function updateChecked(itemId,items) {
  items.map(function(item,index){
    if(item.itemId == itemId) {
      item.checked = !item.checked;
    }
  })
}

function deleteItem(itemId,items) {
  for(let i = 0; i< items.length ;i++) {
    if(items[i].itemId==itemId) {
      items.splice(i, 1);
      i--;
    }else {
      items[i].sqe = i+1;
    }
  }
}

function updateItemSeq(data,datas) {
  for(let i = 0;i<data.length ; i++) {
    for(let j = 0; j<datas.length;j++) {
      if(data[i] == datas[j].itemId) {
        datas[j].sqe= i+1;
      }
    }
  }
}


function createItem(value,items) {
  let obj = {
    itemId: math.random(),
    value : value,
    label: value, 
    checked: value.checked,
    seq: index,
  }
  items.push(obj);
}




class MultiGroup extends Component {
  constructor(props) {
    super(props);
    var value = this.props.datas;
    for (let i = 0;i< value.length ;i++) {
      //1，判断value是字符串还是数组；
      var isObject = isType("Object");
      var isString = isType("String");
    }
    this.state = {
      datas: this.props.datas,
      values: this.props.datas,
    }
  }

  sortItem(data) {
    updateItemSeq(data,this.state.datas);
    console.log(this.state.datas);
    this.setState({
      datas: this.state.datas
    });
  }

  listCheckChange(event) {
    let itemId = event.currentTarget.value;
    updateChecked(itemId,this.state.datas);
    console.log(this.state.datas);
    this.setState({
      datas: this.state.datas
    });
  }

  removeItem(event) {
    let index = event.currentTarget.id;
    deleteItem(index,this.state.datas);
    this.setState({
      datas: this.state.datas
    });
  }

  addItem(value) {
    createItem(value,this.state.datas);
    console.log(this.state.datas);
    this.setState({
      datas: this.state.datas
    });
  }

  render (){
    const { enableSort,enableChecked,enableSearch, outputDataElement} = this.props;
    let  outputDataElementId = outputDataElement + '-' + (Math.random() + "").substr(2);
    return (
      <div className="multi-group">
        <List datas={this.state.datas}  enableChecked ={ enableChecked } enableSort = {enableSort} removeItem={(index)=>this.removeItem(index)}  listCheckChange={(event)=>this.listCheckChange(event)} sortItem={(event=>this.sortItem(event))} />
        <InputGroup enableSearch = { enableSearch } addItem={(value)=>this.addItem(value)} />
        <input type='hidden' id={outputDataElementId} name={outputDataElement} value={JSON.stringify(this.state.datas)} />
      </div>
    );
  }
}

MultiGroup.propTypes = {

};

MultiGroup.defaultProps = {
  className: 'multi-group',
  datas: [],//
  enableSort: true,
  enableSearch: true,
  enableChecked:true,
  outputDataElement:'hidden-input',
};

export default MultiGroup;