import React, { Component } from 'react';
import List from './list';
import InputGroup from './input-group';
import './style.less'


function initItem(dataSourceUi,value,index) {
  let obj = {
    id:Math.random(),
    label: value,
    seq: index,
  }
  dataSourceUi.push(obj);
}

function removeItem(dataSourceUi,id) {
  console.log(dataSourceUi);
  for(let i = 0; i< dataSourceUi.length ;i++) {
    if(dataSourceUi[i].id==id) {
      dataSourceUi.splice(i, 1);
      i--;
      console.log(dataSourceUi);
    }else {
      dataSourceUi[i].seq = i+1;
      console.log(dataSourceUi);
    }
  }
}

function updateItemSeq(data,datas) {
  let temps = [];
  for(let i = 0;i<data.length ; i++) {
    for(let j = 0; j<datas.length;j++) {
      if(data[i] == datas[j].id) {
        datas[j].seq= i+1;
        temps.push(datas[j]);
      }
    }
  }
  return temps;
}

class MultiInput extends Component {
  constructor(props) {
    super(props);
    this.state = {
      dataSourceUi: [],
    }
    this.props.dataSource.map((item,index)=>{
      initItem(this.state.dataSourceUi,item,index+1);
    })
  }

  sortItem(datas) {
    this.state.dataSourceUi = updateItemSeq(datas,this.state.dataSourceUi);
    console.log(this.state.dataSourceUi);
    this.setState({
      dataSourceUi: this.state.dataSourceUi
    });
  }

  removeItem(event) {
    let id = event.currentTarget.id;
    removeItem(this.state.dataSourceUi,id);
    console.log(this.state.dataSourceUi);
    this.setState({
      dataSourceUi: this.state.dataSourceUi
    });
  }

  addItem(value) {
    console.log(this.state.dataSourceUi.length + 1);
    initItem(this.state.dataSourceUi,value,this.state.dataSourceUi.length+1);
    this.setState({
      datas: this.state.dataSourceUi,
    });
  }

  render (){
    const { sortable,enableChecked,searchable, outputDataElement} = this.props;
    let  outputDataElementId = outputDataElement + '-' + (Math.random() + "").substr(2);
    let outputSet = [];
    this.state.dataSourceUi.map((item,index)=>{
      outputSet.push(item.label);
    })
    console.log('render');
    return (
      <div className="multi-group">
        <List dataSourceUi = {this.state.dataSourceUi} removeItem={(id)=>this.removeItem(id)} sortItem={(event=>this.sortItem(event))}></List>
        <InputGroup searchable = { searchable } addItem={(value)=>this.addItem(value)} />
        <input type='hidden' id={outputDataElementId} name={outputDataElement} value={JSON.stringify(outputSet)} />
      </div>
    );
  }
}

console.log(React.PropTypes.array);

MultiInput.propTypes = {
  dataSource: React.PropTypes.array,
};

MultiInput.defaultProps = {
  className: 'multi-group',
  dataSource: [],//必须是数组
  sortable: true,//必须是bool
  searchable: false,//必须是bool
  outputDataElement:'hidden-input',//必须是string,
};

export default MultiInput;