import React, { Component } from 'react';

export default class List extends Component {
  static getDefaultProps = {
    removeItem: ev => {
    }
  }

  static propTypes = {
    removeItem: React.PropTypes.func.isRequired,
  };

  render() {
    var List = this.props.list.map( (item,i) => {
      return (
        <li className="list-group-item mbs" key={i}>{item}
          <a className="pull-right" onClick={event=>this.props.removeItem(event)} id={i}>
            <i className = "es-icon es-icon-close01"></i>
          </a>
        </li>
      );
    });
    return (
      <ul className="list-group teacher-list-group sortable-list mb0">{List}</ul>
    );
  }
};