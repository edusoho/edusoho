import React, { Component } from 'react';

export default class Options extends Component {
  constructor(props){
    super(props);
    this.state = {
      haveSearchResults: this.props.haveSearchResults,
    }
  }
  render() {
    console.log(this.props.items);
    return (
      <ul className={`dropdown-menu options ${ this.state.haveSearchResults && 'show' } `}>
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
      haveSearchResults: false,
    });
    this.props.selectChange(event);
  }
}