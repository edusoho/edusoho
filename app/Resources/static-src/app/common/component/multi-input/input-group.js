import React, { Component } from 'react';
import { trim } from '../../unit';
import { send } from '../../server';
import Options from './options';

export default class InputGroup extends Component {
  constructor(props) {
    super(props);
    this.state = {
      itemName: "",
      searched: true,
      resultful: false,
      searchResult:[],
    }
    this.searchable = this.props.searchable.enable;
    this.addable = this.props.addable;
    this.searchableUrl = this.props.searchable.url;
    console.log(this.props);
  }

  selectChange(event) {
    //在这种情况下，不应该去搜索，那什么时候再搜索呢；
    this.props.onSearch(event.target.id);
    this.setState({
      searched: false,
      itemName: event.target.innerHTML,
    })
  }

  onFocus(event) {
    //在这种情况下，重新开启搜索功能；
    this.setState({
      searched: true,
    })
  }

  handleNameChange (event){
    let value = trim(event.currentTarget.value);
    this.setState({
      itemName: value,
      searchResult: [],
      resultful:false,
    });

    if(this.searchable && value.length > 0 && this.state.searched) {
      setTimeout(()=>{
        console.log('seach start..');
        send(this.searchableUrl+value,searchResult=>{
          console.log({'seach url':this.searchableUrl+value});
          if(this.state.itemName.length>0) {
            console.log({'searchResult': searchResult});
            this.setState({
              // searchResult:[{avatar:"/files/user/2016/11-22/17385936ebcd728942.jpg?7.3.4",id:"1526",isVisible:1,nickname:"wuli"}],
              searchResult: searchResult,
              resultful:true,
            });
            console.log({'searchResult':this.state.searchResult});
          }
        });
      },100)
    }
  }

  handleAdd()  {
    if(this.state.itemName.length>0) {
      //@TODO序号应该再哪里去加；
      this.props.addItem(this.state.itemName);
    }
    this.setState({
      itemName:'',
      searchResult:[],
      resultful: false,
    })
  }

  render (){
    return (
      <div className="input-group">
        <input className="form-control" value={this.state.itemName} onChange={event => this.handleNameChange(event)} onFocus = {event=>this.onFocus(event)}  />
        { this.searchable && this.state.resultful && <Options searchResult ={this.state.searchResult} selectChange ={(event)=>this.selectChange(event)} resultful={this.state.resultful}/> }
        { this.addable && <span className="input-group-btn"><a className="btn btn-default" onClick={()=>this.handleAdd()}>添加</a></span> }
      </div>
    );
  }
}