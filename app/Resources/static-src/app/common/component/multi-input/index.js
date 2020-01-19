import React, { Component } from 'react';
import List from './list';
import InputGroup from './input-group';
import { getRandomString } from './part';

function initItem(dataSourceUi, value) {
  let item = {
    itemId: getRandomString(),
    label: value,
    outputValue: value
  };
  dataSourceUi.push(item);
}

function removeItem(dataSourceUi, itemId) {
  for (let i = 0; i < dataSourceUi.length; i++) {
    if (dataSourceUi[i].itemId == itemId) {
      dataSourceUi.splice(i, 1);
      break;
    } 
  }
}

function updateItemSeq(sortDatas, dataSourceUi) {
  let temps = [];
  for (let i = 0; i < sortDatas.length; i++) {
    for (let j = 0; j < dataSourceUi.length; j++) {
      if (sortDatas[i] == dataSourceUi[j].itemId) {
        temps.push(dataSourceUi[j]);
        break;
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
    }
    this.props.dataSource.map((item, index) => {
      initItem(this.state.dataSourceUi, item);
    })

    console.log({'initItem after':this.state.dataSourceUi})
  }

  getChildContext() {
    return {
      removeItem: this.removeItem,
      sortItem: this.sortItem,
      addItem: this.addItem,
      addable: this.props.addable,
      searchable: this.props.searchable,
      sortable: this.props.sortable,
      listClassName:this.props.listClassName,
      inputName: this.props.inputName,
      dataSourceUi: this.state.dataSourceUi,
    }
  }

  removeItem = (event) => {
    let id = event.currentTarget.attributes["data-item-id"].value;
    removeItem(this.state.dataSourceUi, id);
    this.setState({
      dataSourceUi: this.state.dataSourceUi,
    });
  }

  sortItem = (datas) => {
    this.state.dataSourceUi = updateItemSeq(datas, this.state.dataSourceUi);
    this.setState({
      dataSourceUi: this.state.dataSourceUi,
    });
  }

  addItem = (value, data) => {
    initItem(this.state.dataSourceUi, value);
    this.setState({
      dataSourceUi: this.state.dataSourceUi,
    });
  }

  getOutputSets() {
    //应该优化成表单数据进行填充
    let outputSets = [];
    this.state.dataSourceUi.map((item, index) => {
      outputSets.push(item.outputValue);
    })
    return outputSets;

  }

  getList() {
    return (<List></List>);
  }

  render() {
    let list = this.getList();
    let outputSets = this.getOutputSets();
    return (
      <div className="multi-group">
        {list}
        {this.props.showAddBtnGroup && <InputGroup/>}
        <input type='hidden' name={this.props.outputDataElement} value={JSON.stringify(outputSets)} />
      </div>
    );
  }
}

MultiInput.propTypes = {
  multiInputClassName: React.PropTypes.string,
  listClassName: React.PropTypes.string,
  dataSource: React.PropTypes.array.isRequired,
  sortable: React.PropTypes.bool,
  addable: React.PropTypes.bool,
  searchable: React.PropTypes.shape({
    enable: React.PropTypes.bool,
    url: React.PropTypes.string,
  }),
  showAddBtnGroup: React.PropTypes.bool,
  inputName: React.PropTypes.string,
  checkBoxName: React.PropTypes.string,
  outputDataElement: React.PropTypes.string,//带删除字段
};

MultiInput.defaultProps = {
  multiInputClassName: 'multi-group',
  listClassName: '',
  dataSource: [],
  sortable: true,
  addable: true,
  searchable: {
    enable: false,
    url: '',
  },
  showAddBtnGroup: true,
  inputName: '',
  checkBoxName: 'visible_',
  outputDataElement: 'hidden-input',//带删除字段
};

MultiInput.childContextTypes = {
  removeItem: React.PropTypes.func,
  sortItem: React.PropTypes.func,
  addItem: React.PropTypes.func,
  addable: React.PropTypes.bool,
  searchable:  React.PropTypes.shape({
    enable: React.PropTypes.bool,
    url: React.PropTypes.string,
  }),
  sortable: React.PropTypes.bool,
  listClassName:React.PropTypes.string,
  inputName: React.PropTypes.string,
  dataSourceUi: React.PropTypes.array.isRequired,
};


