import React, { Component } from 'react';
import List from './list';
import InputGroup from './input-group';
import './style.less'

function isType(type) {
  return function(obj) {
    return {}.toString.call(obj) == "[object " + type + "]"
  }
}

function updateChecked(itemId,items,checkedName) {
  items.map(function(item,index){
    if(item.itemId == itemId) {
      item.checked = !item.checked;
      item.outputDatas.checkedName = item.checked;
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

function initItem(items,outputDatas,outputData,value,sqe=0,checked=true) {
  if(!outputDatas) {
    outputDatas = [];
  }
  outputDatas.push(outputData);
  let obj = {
    itemId :Math.random(),
    value :value,
    sqe: sqe,
    checked: checked,
    outputData: outputData,
  }
  items.push(obj);
  console.log(items);
}

class MultiGroup extends Component {
  constructor(props) {
    super(props);
    let datas = [];
    let outputDatas = [];
    this.propsItems = this.props.datas;
    this.propsItemType = '';

    if(this.propsItems.length > 0 ) {
      //有数据的情况下；
      for (let i = 0;i < this.propsItems.length ;i++) {
        let propsItem = this.propsItems[i];
        if(isType("Object")(propsItem)) {
          this.propsItemType = 'Object';
          initItem(datas,outputDatas,propsItem,propsItem[this.props.valueName],i,propsItem[this.props.checkedName]);
          //如何输出propsItems
        }else if(isType("String")(propsItem)) {
          this.propsItemType = 'String';
          initItem(datas,outputDatas,propsItem,propsItem,i);
          //如何输出propsItems
        }
      }
    }

    this.state = {
      datas: datas,
      outputDatas:outputDatas,
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
    updateChecked(itemId,this.state.datas,this.props.checkedName);
    //

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
    let outputData ;
    if(this.propsItemType === "Object") {
      outputData[this.props.valueName] = value;
      if(this.props.enableChecked) {
        outputData[this.props.checkedName] = true;
      }
    } else {
      outputData = value;
    }

    initItem(this.state.datas,this.state.datas.outputDatas,outputData,value,this.state.datas.length + 1);
    this.setState({
      datas: this.state.datas
    });
  }

  render (){
    const { enableSort,enableChecked,enableSearch, outputDataElement} = this.props;
    let  outputDataElementId = outputDataElement + '-' + (Math.random() + "").substr(2);

    let outputDatas = [];
    for( let i = 0 ; i< this.state.datas.length;i++) {
      outputDatas.push(this.state.datas[i].outputData);
    }


    return (
      <div className="multi-group">
        <List datas={this.state.datas}  enableChecked ={ enableChecked } enableSort = {enableSort} removeItem={(index)=>this.removeItem(index)}  listCheckChange={(event)=>this.listCheckChange(event)} sortItem={(event=>this.sortItem(event))} />

        {this.state.list}
        <InputGroup enableSearch = { enableSearch } addItem={(value)=>this.addItem(value)} />
        <input type='hidden' id={outputDataElementId} name={outputDataElement} value={JSON.stringify(outputDatas)} />
      </div>
    );
  }
}

MultiGroup.propTypes = {

};

MultiGroup.defaultProps = {
  className: 'multi-group',
  datas: [],//必须是数组
  enableSort: true,//必须是bool
  enableSearch: false,//必须是bool
  outputDataElement:'hidden-input',//必须是string,
};

export default MultiGroup;