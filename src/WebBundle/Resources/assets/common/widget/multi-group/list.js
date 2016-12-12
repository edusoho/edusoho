import React, { Component } from 'react';

export default class List extends Component {
  constructor(props) {
    super(props);
    this.state = {
      datas: this.props.datas
    }
  }

  componentDidMount(){
    let enableSort = this.props.enableSort;
    let $list = $('.sortable-list').sortable(Object.assign({}, {
      element: '.sortable-list',
      distance: 20,
      delay: 100,
      itemSelector: "li",
      onDrop: (item, container, _super) =>{
        _super(item, container);
        var data = $list.sortable("serialize").get();
        this.props.sortItem(data);
      },
      serialize: function(parent, children, isContainer) {
        return isContainer ? children : parent.attr('id');
      }
    }));
  };

  render() {
    const { enableChecked } =  this.props;
    return (
      <ul className="list-group teacher-list-group sortable-list mb0">
      {
        this.state.datas.map( (item,i) => {
          return (
            <li className="list-group-item mbs" key={i} id={item.itemId} data-sqe={item.sqe}>
              {item.value}
              { enableChecked && <input type="checkbox" value={item.itemId} checked={item.checked} onChange = {event=>this.props.listCheckChange(event)}/>}
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