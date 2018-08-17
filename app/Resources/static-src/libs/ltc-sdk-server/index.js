import Api from './api';
import EsMessenger from 'app/common/messenger';

class LtcSDKServer {
  constructor() {
    this.options = {};
    this.resource = $.parseJSON($('#ltc-source-list').text());
    this.childrenList = this.getChildrenList();

    this.messenger = new EsMessenger({
      name: 'parent',
      project: 'LtcProject',
      children: this.getChildren(),
      type: 'parent'
    });

    this.event();
  }

  getChildrenList() {
    let childs = [];
    ['task-create-content-iframe', 'task-create-finish-iframe', 'task-content-iframe'].forEach(function(value){
      if ($('#'+value).length > 0) {
        childs.push(value);
      }
    });

    return childs;
  }

  getChildren() {
    let childs = [];
    this.childrenList.forEach(function(value){
      childs.push(document.getElementById(value));
    });

    return childs;
  }

  event() {
    this.messenger.on('init', ()=> {
      this.childrenList.forEach((value) => {
        this.emitChild(value, 'initResourceList', this.resource);
      })
    });
  }

  on(eventName, args) {
    this.messenger.on(eventName, args);
  }

  emitChild(id, eventName, args) {
    this.messenger.sendToChild({id: id}, eventName, args);
  }

  config(options) {
    let DEFAULTS = {
      apiList: [],
      appId: null,
    }
    Object.assign(this.options, DEFAULTS, options);

    return this;
  }

  getApi(options) {
    return Api(options);
  }
}

module.exports = window.ltc = new LtcSDKServer();