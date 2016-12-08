import React, { Component } from 'react';
import List from './list';
import InputGroup from './input-group';

class MultiGroup extends Component {
  constructor(props) {
    super(props);
    this.state = {
      items: this.props.items
    }
  }

  removeItem(index) {
    this.state.items.splice(index,1);
    this.setState({
      items: this.state.items
    });
  }

  addItem(item) {
    this.state.items.push([item]);
    this.setState({
      items: this.state.items
    });
  }

  render (){
    return (
      <div className="panes">
        <List removeItem={(index)=>this.removeItem(index)} list={this.state.items}  />
        <InputGroup addItem={(item)=>this.addItem(item)}/>
      </div>
    );
  }
}

export default MultiGroup;