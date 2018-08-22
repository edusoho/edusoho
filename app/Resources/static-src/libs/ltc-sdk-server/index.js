import Api from './api';
import EsMessenger from 'jay-post-message';

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

    this.messenger.on('getApi', (msg) => {
      let apiName = msg.name;
      let options = {
        headers: {
          'Accept': 'application/vnd.edusoho.v2+json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': $('meta[name=csrf-token]').attr('content')
        },
      };

      this.getApi(options)[apiName](msg).then(response => {
        let results = response.data;
        results.uuid = msg.uuid;
        this.emitChild(msg.iframeId, "returnApi", results);
      }, error => {
        console.log(error);
      });
    })
  }

  off(eventName) {
    this.messenger.off(eventName);
  }

  on(eventName, args) {
    this.messenger.on(eventName, args);
  }

  once(eventName, args) {
    this.messenger.once(eventName, args);
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