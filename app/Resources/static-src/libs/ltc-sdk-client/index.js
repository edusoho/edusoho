import Api from './api';
import EsMessenger from 'app/common/messenger';

class LtcSDKClient {
  constructor() {alert
    this.options = {};
    this.messenger =  new EsMessenger({
      name: 'task-create-content-iframe',
      project: 'LtcProject',
      children: [],
      type: 'child'
    });

    this.init();
  }

  
  init() {
    this.messenger.sendToParent('init');
    this.messenger.on('initResourceList', function(value) {
      alert('子页面收到父页面消息');
    });
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

module.exports = window.ltcsdkcLtcSDKClientlient = ltcsdk;
