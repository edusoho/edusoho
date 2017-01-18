import React, { Component } from 'react';
import sortList from 'common/sortable';
import { getRandomString } from './part';

export default class List extends Component {
  constructor(props) {
    super(props);
    this.listId = getRandomString();
  }

  componentDidMount(){
    if(this.props.sortable) {
      sortList({
        element:`#${this.listId}`,
        itemSelector: "li",
        ajax: false,
      },(data) =>{
        this.context.sortItem(data);
      });
    }
  }

  render() {
    const { dataSourceUi,sortable } =  this.props;
    let name = '';
    if( dataSourceUi.length > 0 ) {
      name = 'list-group';
    }
    return (
      <ul id={this.listId} className={`multi-list sortable-list ${name} ${this.props.listClassName}`} >
      {
        dataSourceUi.map( (item,i) => {
          return (
            <li className="list-group-item" id={item.itemId} key={i} data-seq={item.seq}>
              <i className={ sortable ? 'es-icon es-icon-yidong mrl color-gray inline-block vertical-middle' : hidden }></i>
              <span className="label-name text-overflow inline-block vertical-middle">{ item.label }</span>
              <a className="link-gray mts pull-right" onClick={event=>this.context.removeItem(event)}  data-item-id={item.itemId}>
                <i className = "es-icon es-icon-close01 inline-block vertical-top text-12"></i>
              </a>
            </li>
          )
        })
      }
      </ul>
    )
  }
};

List.contextTypes = {
  removeItem: React.PropTypes.func,
  sortItem: React.PropTypes.func,
};