import React, { Component } from 'react';
import List from './list';
import InputGroup from './input-group';
import './style.less'



//items 数据列表，1、isSor是否拖动排序，输入时是否可搜索（true,false）,是否选中
//list 数据为什么要放到父组件本身呢》
//

function updateChecked(id,items) {
  items.map(function(item,index){
    if(item.id == id) {
      item.checked = !item.checked;
    }
  })
}

function deleteItem(id,items) {
  items.map(function(item,index){
    if(item.id==id) {
      items.splice(index, 1);
    }
  })
}


function createItem(value,items) {
  let obj = {
    id: items.length + 1,
    value : value,
    checked: false,
    sqe: items.length + 1,
  }
  items.push(obj);
}



class MultiGroup extends Component {
  constructor(props) {
    super(props);
    this.state = {
      datas: this.props.datas,
    }
    console.log(this.props.datas);
    // this.state = {
    //   items: this.props.items,
    //   key: this.props.fieldName + '-' + (Math.random() + "").substr(2)
    // }
  }

  listCheckChange(event) {
    let id = event.currentTarget.value;
    updateChecked(id,this.state.datas);
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
    this.setState({
      datas: this.state.datas
    });
  }

  render (){
    const { enableSort,enableChecked,enableSearch, outputDataElement} = this.props;
    let  outputDataElementId = outputDataElement + '-' + (Math.random() + "").substr(2);
    return (
      <div className="multi-group">
        <List datas={this.state.datas}  enableChecked ={ enableChecked } enableSort = {enableSort} removeItem={(index)=>this.removeItem(index)}  listCheckChange={(event)=>this.listCheckChange(event)} />
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
  datas: [],
  enableSort: false,
  enableSearch: false,
  enableChecked:false,
  outputDataElement:'',
};

// <List removeItem={(index)=>this.removeItem(index)} datas={this.state.datas}  enableSort = {enableSort} sortable={this.props.sortable}  compKey={this.state.key} />



export default MultiGroup;