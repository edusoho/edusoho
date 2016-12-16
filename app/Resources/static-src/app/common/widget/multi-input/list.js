import React, { Component } from 'react';
import sortList from 'common/sortable';

export default class List extends Component {
  constructor(props) {
    super(props);
    this.listId = Math.random().toString().replace('.','');
  }

  componentDidMount(){
    if(this.props.sortable) {
      sortList({
        element:`#${this.listId}`,
        itemSelector: "li",
        ajax: false,
      },(data) =>{
        this.props.sortItem(data);
      });
    }
  }

  render() {
    const { dataSourceUi } =  this.props;
    let name = '';
    if( dataSourceUi.length > 0 ) {
      name = 'list-group';
    }
    return (
      <ul id={this.listId} className={`multi-list ${name} ${this.props.listClassName}`}>
      {
        dataSourceUi.map( (item,i) => {
          return (
            <li className="list-group-item" id={item.itemId} key={item.itemId} data-seq={item.seq}>
              {item.label}
              <a className="pull-right" onClick={event=>this.props.removeItem(event)} id={item.itemId}>
                <i className = "es-icon es-icon-close01"></i>
              </a>
            </li>
          )
        })
      }
      </ul>
    )
  }
};