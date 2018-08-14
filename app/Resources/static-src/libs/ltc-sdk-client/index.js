import Api from './api';
import EsMessenger from 'app/common/messenger';

class LtcSDKClient {
  constructor() {
    this.options = {};
    this.handler = {};
    this.isVerify = false;
    this.messenger = null;
    this.init();
  }

  inti() {
    // 初始化资源路径
  }

  load(urls) {
    let promises = [];

    urls.forEach(function(value) {
      let promise = new Promise(function(resolve, reject) {
        var script = document.createElement('script');

        script.src = value;
        script.addEventListener('load', function() {
          resolve(script);
        }, false);

        script.addEventListener('error', function() {
          reject(script);
        }, false);

        document.body.appendChild(script);
      });

      promises.push(promise);
    });

    
    Promise.all(promises)
  }

  getMessenger() {
    return (this.messenger === null) ? new EsMessenger({
      name: 'partner',
      project: 'LtcProject',
      children: [],
      type: 'child'
    }) : this.messenger;
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

let ltcsdk = new LtcSDKClient();

module.exports = window.ltcsdkclient = ltcsdk;