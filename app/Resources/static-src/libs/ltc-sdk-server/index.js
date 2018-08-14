import Api from './api';
import EsMessenger from 'app/common/messenger';

class LtcSDKServer {
  constructor() {
    this.options = {};
    this.resource = $.parseJSON($('#ltc-source-list').text());
    this.messenger = new EsMessenger({
      name: 'parent',
      project: 'LtcProject',
      children: [$('#task-create-content-iframe')[0]],
      type: 'parent'
    });

    this.event();
  }

  event() {
    this.messenger.on('init', ()=> {
      this.messenger.sendToChild('init', this.resource);
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