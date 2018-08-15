import Api from './api';
import EsMessenger from 'app/common/messenger';

class LtcSDKServer {
  constructor() {
    this.options = {};
    this.resource = $.parseJSON($('#ltc-source-list').text());
    this.messenger = new EsMessenger({
      name: 'parent',
      project: 'LtcProject',
      children: [document.getElementById('task-create-content-iframe'),document.getElementById('task-create-finish-iframe')],
      type: 'parent'
    });

    this.event();
  }

  event() {
    this.messenger.on('init', ()=> {
      this.messenger.sendToChild({id: 'task-create-content-iframe'}, 'initResourceList', this.resource);
      this.messenger.sendToChild({id: 'task-create-finish-iframe'}, 'initResourceList', this.resource);
    });
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

let ltcsdk = new LtcSDKServer();

module.exports = window.ltcsdkserver = ltcsdk;