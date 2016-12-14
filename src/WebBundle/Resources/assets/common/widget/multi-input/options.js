import React, { Component } from 'react';

export default class Options extends Component {
  constructor(props){
    super(props);
    this.state = {
      resultful: this.props.resultful,
    }
  }
  render() {
    let options = [];
    this.props.searchResult.map((item,i) => {
      let temp = (<li key={item.id}><a id={JSON.stringify(item)} onClick={event=>this.handleChange(event)}>{item.nickname}</a></li>);
      options.push(temp);
    })
    if(options.length <= 0) {
      options.push((<li className="not-find"><a>未找到...</a></li>));
    }
    return (
      <ul className={`dropdown-menu options ${ this.state.resultful && 'show' } `}>
      {options}
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