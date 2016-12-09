import React, { Component } from 'react';

export default class List extends Component {
  static getDefaultProps = {
    removeItem: ev => {
    }
  }

  static propTypes = {
    removeItem: React.PropTypes.func.isRequired,
  }

  constructor(props) {
    super(props);
    this.state = {
      items: this.props.list
    }
  }

  componentDidMount(){
    if(!this.props.sortable){
      return;
    }
    let self = this;
    
    let $sortComp = $('.sortable-list').sortable(Object.assign({}, {
      element: '.sortable-list',
      distance: 20,
      delay: 100,
      onDrop: function(item, container, _super){
        _super(item, container);
        //也许应该使用redux之类的东东进行组件间数据通讯
        // self.setState({items: $sortComp.sortable('serialize').get()});
        $(document).trigger('items-sorted', [self.props.compKey, $sortComp.sortable('serialize').get()]);
      },
      serialize: function(parent, children, isContainer) {
        return isContainer ? children : parent.find('span').text();
      }
    }));
  };

  render() {
    var List = this.state.items.map( (item,i) => {
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