import React, { Component } from 'react';

export default class Options extends Component {
  constructor(props){
    super(props);
    this.state = {
      resultful: this.props.resultful,
    };
  }
  render() {
    let options = [];
    this.props.searchResult.map((item,i) => {
      let temp = (<li key={ i }><a data-item = {JSON.stringify(item)} onClick={event=>this.handleChange(event)} data-name={item.nickname}>{item.nickname}</a></li>);
      options.push(temp);
    });
    if(options.length <= 0) {
      options.push((<li className="not-find"><a>{ Translator.trans('site.data.not_found') }</a></li>));
    }
    return (
      <ul className={`dropdown-menu options ${ this.state.resultful && 'show' } `}>
        {options}
      </ul>
    );
  }

  handleChange (event) {
    this.setState({
      resultful: false,
    });
    let data = event.currentTarget.attributes['data-item'].value;
    this.props.selectChange(event.currentTarget.attributes['data-name'].value,JSON.parse(data));
  }
}