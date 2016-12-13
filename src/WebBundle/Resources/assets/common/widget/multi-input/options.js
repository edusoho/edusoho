import React, { Component } from 'react';

export default class Options extends Component {
  constructor(props){
    super(props);
    this.state = {
      resultful: this.props.resultful,
    }
  }
  render() {
    return (
      <ul className={`dropdown-menu options ${ this.state.resultful && 'show' } `}>
      {
        this.props.searchResult.map((item,i) => {
          return <li key={item.id}><a id={JSON.stringify(item)} onClick={event=>this.handleChange(event)}>{item.nickname}</a></li>
        })
      }
      </ul>
    )
  }

  handleChange (event) {
    this.setState({
      resultful: false,
    });
    this.props.selectChange(event);
  }
}