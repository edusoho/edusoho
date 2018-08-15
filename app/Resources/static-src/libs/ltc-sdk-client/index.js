import Api from './api';
import EsMessenger from 'app/common/messenger';
const resources = require('./resource.js');
require('libs/iframe-resizer-contentWindow.js');

class LtcSDKClient {
  constructor() {
    this.options = {};
    this.messenger =  new EsMessenger({
      name: self.frameElement.getAttribute('id'),
      project: 'LtcProject',
      children: [],
      type: 'child'
    });
    this.resource = {};
  }

  async loadCss(url = 'bootstrap-css') {
    await this._initResourceList();

    let link = document.createElement('link');
    link.type = 'text/css';
    link.rel = 'stylesheet';
    link.href = this.resourceList[url];
    let head = document.getElementsByTagName('head')[0];
    head.appendChild(link);
  }

  async load(...urls) {
    let self = this;
    await self._initResourceList();
    for (let value of urls) {
      await new Promise(function(resolve, reject) {
        if (self.resource[value]) {
          resolve(value);
        }

        let script = document.createElement('script');
        script.src = self.resourceList[value];
        script.addEventListener('load', function() {
          if (resources['init_'+value]) {
            resources['init_'+value]();
          }
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

  on(eventName, args={}) {
    this.messenger.on(eventName, args);
  }

  emit(eventName, args={}) {
    args = Object.assign({
      iframeId: self.frameElement.getAttribute('id'),
    }, args);
    this.messenger.sendToParent(eventName, args);
  }

  getApi(options) {
    return Api(options);
  }

  _initResourceList() {
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
}

module.exports = window.ltc = new LtcSDKClient();
