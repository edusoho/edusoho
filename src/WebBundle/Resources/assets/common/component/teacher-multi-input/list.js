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
      console.log($(`#${this.listId}`));
    }
  }

  render() {
    const { dataSourceUi } = this.props;
    return (
      <ul id={this.listId} className={`${this.props.listClassName} sortable-list list-group mb0`}>
      {
        dataSourceUi.map( (item,i) => {
          return (
            <li className="list-group-item mbs" id={item.itemId} key={item.itemId} data-seq={item.seq}>
              <img src ={item.avatar}/> 
              {item.nickname}
              <label><input type="checkbox" checked={item.isVisible} onChange= {event=>this.props.onChecked(event)} value={item.itemId}/>显示</label>
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