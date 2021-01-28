import React, { Component } from 'react';
import { trim } from '../../unit';
import { send } from '../../server';
import Options from './options';
import postal from 'postal';

export default class InputGroup extends Component {
  constructor(props) {
    super(props);
    this.state = {
      itemName: '',
      searched: true,
      resultful: false,
      searchResult: [],
    };
    this.timer = false;
    this.subscribeMessage();
  }

  subscribeMessage() {
    postal.subscribe({
      channel: 'courseInfoMultiInput',
      topic: 'addMultiInput',
      callback: () => {
        console.log('add');
        this.handleAdd();
      }
    });
  }

  selectChange(name, data) {
    if (data) {
      this.context.addItem(name, data);
    }
    this.setState({
      itemName: '',
    });
  }

  onFocus(event) {
    //在这种情况下，重新开启搜索功能；
    this.setState({
      searched: true,
    });
  }

  handleNameChange(event) {
    // let value = trim(event.currentTarget.value);
    let value = event.currentTarget.value;
    this.setState({
      itemName: value,
      searchResult: [],
      resultful: false,
    });

    if (!this.context.searchable.enable || value.length < 0 || !this.state.searched) {
      return;
    }

    if (this.timer) clearTimeout(this.timer);
    this.timer = setTimeout(() => {
      send(this.context.searchable.url + value, searchResult => {
        if (this.state.itemName.length > 0) {
          console.log({ 'searchResult': searchResult });
          this.setState({
            searchResult: searchResult,
            resultful: true,
          });
        }
      });
    }, 500);
  }

  handleAdd() {
    if (trim(this.state.itemName).length > 0) {
      this.context.addItem(this.state.itemName, this.state.itemData);
    }
    this.setState({
      itemName: '',
      searchResult: [],
      resultful: false,
    });
  }

  render() {
    let createTrans = Translator.trans('site.data.create');
    return (
      <div className="input-group">
        <input className="form-control" value={this.state.itemName} onChange={event => this.handleNameChange(event)} onFocus={event => this.onFocus(event)} />
        {this.context.searchable.enable && this.state.resultful && <Options searchResult={this.state.searchResult} selectChange={(event, name) => this.selectChange(event, name)} resultful={this.state.resultful} />}
        {this.context.addable && <span className="input-group-btn"><a className="btn btn-default" onClick={() => this.handleAdd()}>{createTrans}</a></span>}
      </div>
    );
  }
}

InputGroup.contextTypes = {
  addItem: React.PropTypes.func,
  addable: React.PropTypes.bool,
  searchable:  React.PropTypes.shape({
    enable: React.PropTypes.bool,
    url: React.PropTypes.string,
  }),
};
