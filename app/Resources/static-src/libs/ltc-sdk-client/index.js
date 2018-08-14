import Api from './api';
import EsMessenger from 'app/common/messenger';

class LtcSDKClient {
  constructor() {
    this.options = {};
    this.messenger =  new EsMessenger({
      name: 'task-create-content-iframe',
      project: 'LtcProject',
      children: [],
      type: 'child'
    });
    this.resource = {};
  }

  
  initResourceList() {
    let self = this;
    return new Promise(function(resolve, reject) {
      if (self.resourceList) {
        resolve();
      }
      self.messenger.sendToParent('init');
      self.messenger.on('initResourceList', function(data) {
        self.resourceList = data;
        resolve();
      });
    });
  }

  async loadCss() {
    await this.initResourceList();

    let link = document.createElement('link');
    link.type = 'text/css';
    link.rel = 'stylesheet';
    link.href = this.resourceList['codeage-design-css'];
    let head = document.getElementsByTagName('head')[0];
    head.appendChild(link);
  }

  async load(...urls) {
    let self = this;
    await self.initResourceList();
    for (let value of urls) {
      await new Promise(function(resolve, reject) {
        if (self.resource[value]) {
          resolve(value);
        }

        let script = document.createElement('script');
        script.src = self.resourceList[value];
        script.addEventListener('load', function() {
          resolve(value);
          self.resource[value] = true;
        }, false);

        script.addEventListener('error', function() {
          reject(value);
        }, false);

        document.body.appendChild(script);
      });
    }
  };

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

let ltc = new LtcSDKClient();

module.exports = window.ltc = ltc;
