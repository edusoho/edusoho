import React, { Component } from 'react';
import List from './list';
import InputGroup from './input-group';

class MultiGroup extends Component {
  constructor(props) {
    super(props);
    this.state = {
      items: this.props.items,
      key: this.props.fieldName + '-' + (Math.random() + "").substr(2)
    }
  }

  componentDidMount(){
    if(!this.props.sortable){
      return;
    }
    let self = this;
    $(document).bind('items-sorted', function(event, key, sortedItems){
      if(self.state.key !== key){
        return;
      }
      self.setState({
        items: sortedItems
      });
    });
  }

  removeItem(index) {
    this.state.items.splice(index,1);
    this.setState({
      items: this.state.items
    });
  }

  addItem(item) {
    this.state.items.push(item);
    this.setState({
      items: this.state.items
    });
  }

  render (){
    return (
      <div className="panes">
        <List removeItem={(index)=>this.removeItem(index)} compKey={this.state.key} sortable={this.props.sortable} list={this.state.items}  />
        <InputGroup addItem={(item)=>this.addItem(item)}/>
        <input type='hidden' id={this.state.key} name={this.props.fieldName} value={JSON.stringify(this.state.items)} />
      </div>
    );
  }
}

export default MultiGroup;