import React, { Component } from 'react';
import sortList from 'common/sortable';
import { getRandomString } from './part';

export default class List extends Component {
  constructor(props) {
    super(props);
    this.listId = getRandomString();
    this.$list = null;
    this.$item = null;
  }

  componentDidMount() {
    let sortId = `#${this.listId}`;
    this.$list = $(sortId);
    if (this.props.sortable) {
      sortList({
        element: sortId,
        itemSelector: "li",
        ajax: false,
      }, (data) => {
        //@TODO需优化成React的组件
        $(sortId).children().remove();
        $(sortId).append(this.$item);
        this.context.sortItem(data);
      });
      this.onChange(sortId);
    }
  }

  onChange(sortId) {
    //sortList操作了真实的DOM需要还原；
    this.$list.on('mousedown', 'li', () => {
      this.$item = $(sortId).children('.list-group-item');
    })
  }

  componentWillMount() {
    this.$item = $(this.listId).children().clone();
  }



  render() {
    const { dataSourceUi, sortable } = this.props;
    let name = '';
    if (dataSourceUi.length > 0) {
      name = 'list-group';
    }
    return (
      <ul id={this.listId} className={`multi-list sortable-list ${name} ${this.props.listClassName}`} >
        {
          dataSourceUi.map((item, i) => {
            return (
              <li className="list-group-item" id={item.itemId} key={i} data-seq={item.seq}>
                <i className={sortable ? 'es-icon es-icon-yidong mrl color-gray inline-block vertical-middle' : hidden}></i>
                <span className="label-name text-overflow inline-block vertical-middle">{item.label}</span>
                <a className="link-gray mts pull-right" onClick={event => this.context.removeItem(event)} data-item-id={item.itemId}>
                  <i className="es-icon es-icon-close01 inline-block vertical-top text-12"></i>
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