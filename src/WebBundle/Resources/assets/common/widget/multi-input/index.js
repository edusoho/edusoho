import React, { Component } from 'react';
import List from './list';
import InputGroup from './input-group';
import './style.less'


function initItem(dataSourceUi,value,index) {
  let obj = {
    itemId:Math.random(),
    label: value,
    seq: index,
    outputValue: value
  }
  dataSourceUi.push(obj);
}

function removeItem(dataSourceUi,itemId) {
  for(let i = 0; i< dataSourceUi.length ;i++) {
    if(dataSourceUi[i].itemId==itemId) {
      dataSourceUi.splice(i, 1);
      i--;
    }else {
      dataSourceUi[i].seq = i+1;
    }
  }
}

function updateItemSeq(data,datas) {
  let temps = [];
  for(let i = 0;i<data.length ; i++) {
    for(let j = 0; j<datas.length;j++) {
      if(data[i] == datas[j].itemId) {
        datas[j].seq= i+1;
        temps.push(datas[j]);
      }
    }
  }
  return temps;
}

export default class MultiInput extends Component {
  constructor(props) {
    super(props);
  }

  componentWillMount() {
    this.state = {
      dataSourceUi: [],
      list: [],
      outputSets:[],
    }
    this.props.dataSource.map((item,index)=>{
      initItem(this.state.dataSourceUi,item,index+1);
    })
    this.getOutputSets();
    this.state.list = this.getList();
  }

  getList() {
    return (<List dataSourceUi = {this.state.dataSourceUi} removeItem={(itemId)=>this.removeItem(itemId)} sortItem={(event=>this.sortItem(event))}></List>);
  }

  sortItem(datas) {
    this.state.dataSourceUi = updateItemSeq(datas,this.state.dataSourceUi);
    console.log({'sortItem after':this.state.dataSourceUi })
    this.getOutputSets();
    this.setState({
      list: this.getList()
    });
  }

  removeItem(event) {
    let id = event.currentTarget.id;
    removeItem(this.state.dataSourceUi,id);
    console.log({'removeItem after': this.state.dataSourceUi});
    this.getOutputSets();
    this.setState({
      list: this.getList()
    });
  }

  addItem(value) {
    initItem(this.state.dataSourceUi,value,this.state.dataSourceUi.length+1);
    console.log({'addItem after': this.state.dataSourceUi});
    this.getOutputSets();
    this.setState({
      list: this.getList()
    });
  }
  
  getOutputSets(dataSourceUi) {
    this.state.outputSets = [];
    this.state.dataSourceUi.map((item,index)=>{
      this.state.outputSets.push(item.outputValue);
    }) 
    console.log({"outputSets":this.state.outputSets});
    this.setState({
      outputSets: this.state.outputSets,
    })
  }

  render (){
    const { sortable,enableChecked,searchable, outputDataElement} = this.props;
    let  outputDataElementId = outputDataElement + '-' + (Math.random() + "").substr(2);
    return (
      <div className="multi-group">
        {this.state.list}
        <InputGroup searchable = { searchable } addItem={(value)=>this.addItem(value)}  onSearch={(data)=>this.onSearch(data)}  />
        <input type='hidden' id={outputDataElementId} name={outputDataElement} value={JSON.stringify(this.state.outputSets)} />
      </div>
    );
  }
}

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
// <List dataSourceUi = {this.state.dataSourceUi} removeItem={(id)=>this.removeItem(id)} sortItem={(event=>this.sortItem(event))}></List>


