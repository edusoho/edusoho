import React, { Component } from 'react';
import List from './list';
import InputGroup from './input-group';
import { getRandomString } from './part';
import '!style!css!less!./style.less';

function initItem(dataSourceUi,value) {
  let item = {
    itemId: getRandomString(),
    label: value,
    seq: dataSourceUi.length + 1,
    outputValue: value
  };
  dataSourceUi.push(item);
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
    this.state = {
      dataSourceUi: [],
    }
    this.props.dataSource.map((item,index)=>{
      initItem(this.state.dataSourceUi,item);
    })
  }

  getChildContext() {
    return {
      removeItem: this.removeItem,
      sortItem: this.sortItem,
      addItem: this.addItem,
    }
  }

  removeItem = (event) => {
    let id = event.currentTarget.attributes["data-item-id"].value;
    removeItem(this.state.dataSourceUi,id);
    this.setState({
      dataSourceUi: this.state.dataSourceUi,
    });
  }

  sortItem = (datas) => {
    this.state.dataSourceUi = updateItemSeq(datas,this.state.dataSourceUi);
    this.setState({
      dataSourceUi: this.state.dataSourceUi,
    });
  }

  addItem = (value) => {
    initItem(this.state.dataSourceUi,value);
    this.setState({
      dataSourceUi: this.state.dataSourceUi,
    });
  }
  
  getOutputSets() {
    //应该优化成表单数据进行填充
    let outputSets = [];
    this.state.dataSourceUi.map((item,index)=>{
      outputSets.push(item.outputValue);
    }) 
    return outputSets;

  }

  getList() {
    const { sortable,listClassName } = this.props;
    return (<List sortable={ sortable } listClassName={ listClassName } dataSourceUi = {this.state.dataSourceUi}></List>);
  }

  render (){
    const { searchable, addable, outputDataElement} = this.props;
    let list =  this.getList();
    let outputSets = this.getOutputSets();
    return (
      <div className="multi-group">
        {list}
        <InputGroup searchable = { searchable } addable = { addable }/>
        <input type='hidden' name={outputDataElement} value={JSON.stringify(outputSets)} />
      </div>
    );
  }
}

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
};

MultiInput.childContextTypes = {
  removeItem: React.PropTypes.func,
  sortItem: React.PropTypes.func,
  addItem: React.PropTypes.func,
};


