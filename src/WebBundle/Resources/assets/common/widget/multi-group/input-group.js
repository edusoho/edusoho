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
      haveSearchResults: false,
      searchResults:[],
    }
    this.enableSearch = this.props.enableSearch;
  }

  selectChange(event) {
    //在这种情况下，不应该去搜索，那什么时候再搜索呢；
    console.log(event.target.id);
    this.setState({
      searched: false,
      itemName: event.target.id,
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
    console.log(value);
    this.setState({
      itemName: value,
      searchResults: [],
      haveSearchResults:false,
    });

    console.log(this.enableSearch && value.length > 0 && this.state.searched);


    if(this.enableSearch && value.length > 0 && this.state.searched) {
      console.log('seach..');
      send('/course/274/manage/teachersMatch?q='+value,searchResults=>{
        if(this.state.itemName.length>0) {
          console.log("ok");
          this.setState({
            searchResults:[{
              id: 1,
              value: "name",
            }],
            haveSearchResults:true,
          });
          console.log(this.state.searchResults);
        }
      });
    }
  }

  handleAdd()  {
    if(this.state.itemName.length>0) {
      //@TODO序号应该再哪里去加；
      this.props.addItem(this.state.itemName);
    }
    this.setState({
      itemName:'',
      searchResults:[],
      haveSearchResults: false,
    })
  }

  render (){
    console.log(this.state.searchResults);
    console.log(this.enableSearch);
    console.log(this.state.haveSearchResults);

    return (
      <div className="input-group">
        <input className="form-control" value={this.state.itemName} onChange={event => this.handleNameChange(event)} onFocus = {event=>this.onFocus(event)}  />
        { 
          this.enableSearch && this.state.haveSearchResults && <Options items ={this.state.searchResults} selectChange ={(event)=>this.selectChange(event)} haveSearchResults={this.state.haveSearchResults}/>

        }
        <span className="input-group-btn"><a className="btn btn-default" onClick={()=>this.handleAdd()}>添加</a></span>
      </div>
    );
  }
}