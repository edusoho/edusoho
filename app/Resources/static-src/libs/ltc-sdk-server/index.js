import Api from './api';
import EsMessenger from 'app/common/messenger';

class LtcSDKServer {
  constructor() {
    this.options = {};
    this.handler = {};
    this.isVerify = false;
    this.resource = $.parseJSON($('#ltc-source-list').text());
    this.messenger = null;
  }

  getMessenger(children = []) {

   return (this.messenger === null) ? new EsMessenger({
      name: 'parent',
      project: 'LtcProject',
      children: children,
      type: 'parent'
    }) : this.messenger;
   }

  verify() {
    if (!this.isVerify) {
      throw new Error('请先调用config方法，验证身份');
    }
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
    this.verify();
    return Api(options);
  }
}

let ltcsdk = new LtcSDKServer();

module.exports = window.ltcsdkserver = ltcsdk;