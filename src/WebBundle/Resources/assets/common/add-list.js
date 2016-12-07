import React from 'react';

class FriendList extends React.Component {
  render() {
    var List = this.props.list.map( (item,i) => {
      return (<li className="list-group-item" key={item}>{item}<a className="pull-right" onClick={event=>this.deleteItem(event)} id={i}>删除</a></li>);
    });
    return (<ul className="list-group teacher-list-group sortable-list">{List}</ul>);
  }

  deleteItem(event) {
    console.log(event.target.id);
    this.props.removeItem();
  }
};

class AddFriend extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      itemName: ""
    }
  }

  handleNameChange (event){
    this.setState({
      itemName: event.target.value
    })
  }

  handleAdd()  {
    this.props.addItem(this.state.itemName);
    this.setState({
      itemName:''
    })
  }

  render (){
    return (
      <div className="input-group">
        <input className="form-control" value={this.state.itemName} onChange={event => this.handleNameChange(event)} />
        <span className="input-group-btn"><a className="btn btn-default" onClick={()=>this.handleAdd()}>添加</a></span>
      </div>
    );
  }
}

class FriendsContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      items: this.props.items
    }
  }

  removeItem(index) {
    this.state.items.splice(index,1);
    this.setState({
      items: this.state.items
    });
  }

  addItem(item) {
    this.state.items.push([item]);
    this.setState({
      items: this.state.items
    });
  }

  render (){
    return (
      <div className="panes">
        <FriendList removeItem={(index)=>this.removeItem(index)} list={this.state.items}  />
        <AddFriend  addItem={(item)=>this.addItem(item)}/>
      </div>
    );
  }
}

export default FriendsContainer;


