import React, { Component } from 'react';

export default class InputGroup extends Component {
  constructor(props) {
    super(props);
    this.state = {
      itemName: ""
    }
  }

  handleNameChange (event){
    this.setState({
      itemName: event.target.value
    })
  }

  handleAdd()  {
    this.props.addItem(this.state.itemName);
    this.setState({
      itemName:''
    })
  }

  render (){
    return (
      <div className="input-group">
        <input className="form-control" value={this.state.itemName} onChange={event => this.handleNameChange(event)} />
        <span className="input-group-btn"><a className="btn btn-default" onClick={()=>this.handleAdd()}>添加</a></span>
      </div>
    );
  }
}