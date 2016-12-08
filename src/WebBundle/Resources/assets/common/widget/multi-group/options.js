import React, { Component } from 'react';

class Options extends Component {
  constructor(props){
    super(props);
    this.state = {
      showOptions: this.props.showOptions,
    }
  }
  render() {
    console.log(this.props.items);
    return (
      <ul className={`dropdown-menu options ${ this.state.showOptions && 'show' }`}>
      {
        this.props.items.map((item) => {
          return <li key={item.id}><a id={item.value} onClick={event=>this.handleChange(event)}><i>{item.value}</i></a></li>
        })
      }
      </ul>
    )
  }

  handleChange (event) {
    this.setState({
      showOptions: false,
    });
    this.props.selectChange(event);
  }
}