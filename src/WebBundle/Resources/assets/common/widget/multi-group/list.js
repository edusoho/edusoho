import React, { Component } from 'react';

export default class List extends Component {
  constructor(props) {
    super(props);
    this.state = {
      datas: this.props.datas
    }
  }

  componentDidMount(){
    let openSort = this.props.openSort;
    // if(!this.props.sortable){
    //   return;
    // }
    // let self = this;
    
    // let $sortComp = $('.sortable-list').sortable(Object.assign({}, {
    //   element: '.sortable-list',
    //   distance: 20,
    //   delay: 100,
    //   onDrop: function(item, container, _super){
    //     _super(item, container);
    //     //也许应该使用redux之类的东东进行组件间数据通讯
    //     $(document).trigger('items-sorted', [self.props.compKey, $sortComp.sortable('serialize').get()]);
    //   },
    //   serialize: function(parent, children, isContainer) {
    //     return isContainer ? children : parent.find('span').text();
    //   }
    // }));
  };

  render() {
    const { showListCheck } =  this.props;
    console.log(this.state.datas);
    return (
      <ul className="list-group teacher-list-group sortable-list mb0">
      {
        this.state.datas.map( (item,i) => {
          return (
            <li className="list-group-item mbs" key={i}>
              {item.value}
              {showListCheck && <input type="checkbox" value={item.id} checked={item.checked} onChange = {event=>this.props.listCheckChange(event)}/>}
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