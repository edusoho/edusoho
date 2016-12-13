import React, { Component } from 'react';

export default class List extends Component {
  constructor(props) {
    super(props);
  }

  componentDidMount(){
    let sortable = this.props.sortable;
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
    const { dataSourceUi } =  this.props;
    return (
      <ul className="list-group teacher-list-group sortable-list mb0">
      {
        dataSourceUi.map( (item,i) => {
          return (
            <li className="list-group-item mbs" id={item.id} key={item.id} data-seq={item.seq}>
              {item.label}
              <a className="pull-right" onClick={event=>this.props.removeItem(event)} id={item.id}>
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