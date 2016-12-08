import React, { Component } from 'react';
import List from './list';
import InputGroup from './input-group';

class MultiGroup extends Component {
  constructor(props) {
    super(props);
    this.state = {
      items: this.props.items
    }
  }

  componentDidMount(){
    if(!this.props.sortable){
      return;
    }
    //FIXME 这里的拖拽逻辑应该在list.js处理，然后后者通知当前组件更新state.items。
    // let self = this;
    // console.log('sortable : ', this.state.items);
    // $('.sortable-list').sortable(Object.assign({}, {
    //   element: '.sortable-list',
    //   distance: 20,
    //   itemSelector: "li",
    //   onDrop: function(item, container, _super){
    //     _super(item, container);
    //     let _items = $('ul.sortable-list').find('li');
    //     let sortedItems = [];
    //     $.each(_items, function(i,v){
    //       console.log('for each : ', i,v);
    //       sortedItems.push($(v).find('span').text());
    //     });
    //     console.log('sortedItems: ', sortedItems);
    //     self.setState({
    //       items: sortedItems
    //     });
    //   }
    // }));
  }

  removeItem(index) {
    this.state.items.splice(index,1);
    this.setState({
      items: this.state.items
    });
  }

  addItem(item) {
    this.state.items.push(item);
    this.setState({
      items: this.state.items
    });
  }

  render (){
    return (
      <div className="panes">
        <List removeItem={(index)=>this.removeItem(index)} list={this.state.items}  />
        <InputGroup addItem={(item)=>this.addItem(item)}/>
        <input type='hidden' name={this.props.fieldName} value={JSON.stringify(this.state.items)} />
      </div>
    );
  }
}

export default MultiGroup;