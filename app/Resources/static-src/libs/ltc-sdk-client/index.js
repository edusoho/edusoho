import EsMessenger from 'jay-post-message';
const resources = require('./resource.js');
require('libs/iframe-resizer-contentWindow.js');
const apiList = [...require("./api.json").apiList];

class LtcSDKClient {
  constructor() {
    this.options = {};
    this.messenger =  new EsMessenger({
      name: self.frameElement.getAttribute('id'),
      project: 'LtcProject',
      children: [],
      type: 'child'
    });
    this.loadResource = [];
  }

  async loadCss(url = 'bootstrap-css') {
    await this._initResourceList();

    let link = document.createElement('link');
    link.type = 'text/css';
    link.rel = 'stylesheet';
    link.href = this.resource[url];
    let head = document.getElementsByTagName('head')[0];
    head.appendChild(link);
  }

  async load(...urls) {
    let self = this;
    await self._initResourceList();
    for (let value of urls) {
      await new Promise(function(resolve, reject) {
        if (self.loadResource[value]) {
          resolve(value);
        }

        let script = document.createElement('script');
        script.src = self.resource[value];
        script.addEventListener('load', function() {
          if (resources[value]) {
            resources[value]();
          }
          resolve(value);
          self.loadResource[value] = true;
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

  getContext() {
    return this.context;
  }

  getFormSerializeObject($e) {
    let o = {};
    let a = $e.serializeArray();
    $.each(a, function() {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });

    return o;
  }

  once(eventName, args={}) {
    this.messenger.once(eventName, args);
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

  api(options,callback) {

    if (apiList.indexOf(options.name) === -1) {
      return false;
    }

    let uuid = this._getUuid();

    this.emit('getApi', Object.assign(options, {uuid:uuid}));
    this.once(`returnApi_${uuid}`, (results) => {
      callback(results);
    });
  }

  async _initResourceList() {
    let self = this;
    return new Promise(function(resolve, reject) {
      if (self.resource) {
        resolve();
      }

      self.messenger.sendToParent('init');
      self.messenger.once('initResourceList', function(data) {
        self.resource = data['resource'];
        self.context = data['context'];
        document.documentElement.lang = self.context.lang;
        resolve();
      });
    });
  }

  _s4() {
    return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
  }
  _getUuid() {
    return (this._s4()+this._s4()+"-"+this._s4()+"-"+this._s4()+"-"+this._s4()+"-"+this._s4()+this._s4()+this._s4());
  }
}

module.exports = window.ltc = new LtcSDKClient();
