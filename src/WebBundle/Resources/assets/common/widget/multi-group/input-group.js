import React, { Component } from 'react';
import { trim } from '../../unit';
import { send } from '../../server';
import { Options  }  from './options';

export default class InputGroup extends Component {
  constructor(props) {
    super(props);
    this.state = {
      itemName: "",
      isSerach: true,
      canSerach: true,
      haveOptions:false,
      optionsData:[],
    }
  }

  selectChange(event) {
    //在这种情况下，不应该去搜索，那什么时候再搜索呢；
    console.log(event.target.id);
    this.setState({
      canSerach: false,
      itemName: event.target.id,
    })
  }

  onFocus(event) {
    //在这种情况下，重新开启搜索功能；
    this.setState({
      canSerach: true,
    })
  }

  handleNameChange (event){
    let value = trim(event.target.value);
    this.setState({
      itemName: value,
      optionsData: [],
      haveOptions:false,
    });

    if(this.state.isSerach && value.length > 0 && this.state.canSerach) {
      console.log('seach..');
      send('/course/274/manage/teachersMatch?q='+value,optionsData=>{
        if(this.state.itemName.length>0) {
          this.setState({
            optionsData:[{id:1,value:2}],
            haveOptions:true,
          });
        }
      });
    }
  }

  handleAdd()  {
    if(this.state.itemName.length>0) {
      let obj = {
        id: 1,
        value : this.state.itemName,
        checked: true,
        sqe: 0,
      }
      //@TODO序号应该再哪里去加；
      this.props.addItem(obj);
    }
    this.setState({
      itemName:'',
      optionsData:[],
      haveOptions: false,
    })
  }

  render (){
    return (
      <div className="input-group">
        <input className="form-control" value={this.state.itemName} onChange={event => this.handleNameChange(event)} onFocus = {event=>this.onFocus(event)}  />
        { 
          this.state.isSerach && this.state.haveOptions && <Options items ={this.state.optionsData} selectChange ={(event)=>this.selectChange(event)} haveOptions={true}/>
        }
        <span className="input-group-btn"><a className="btn btn-default" onClick={()=>this.handleAdd()}>添加</a></span>
      </div>
    );
  }
}