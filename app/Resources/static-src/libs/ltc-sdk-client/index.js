import EsMessenger from 'es-post-message';
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

  createScript(value, resolve, reject) {
    let script = document.createElement('script');
    script.src = this.resource[value];
    script.addEventListener('load', () => {
      if (resources[value]) {
        resources[value]();
      }
      resolve(value);
      this.loadResource[value] = true;
    }, false);

    script.addEventListener('error', function() {
      reject(value);
    }, false);

    document.body.appendChild(script);
  }

  createCss(value, resolve) {
    let link = document.createElement('link');
    link.type = 'text/css';
    link.rel = 'stylesheet';
    link.href = this.resource[value];
    let head = document.getElementsByTagName('head')[0];
    head.appendChild(link);

    resolve();
  }

  async load(...urls) {
    let self = this;
    await self._init();
    for (let value of urls) {
      await new Promise(function(resolve, reject) {
        if (self.loadResource[value]) {
          resolve(value);
        }
        value.indexOf('.css') >=0 ?  self.createCss(value, resolve) : self.createScript(value, resolve, reject);
      });
    }
  };

  getContext() {
    return this.serverData['context'];
  }

  getEditorConfig() {
    return this.serverData['editorConfig'];
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

  async _init() {
    let self = this;
    return new Promise(function(resolve, reject) {
      if (self.serverData) {
        resolve();
      }

      self.messenger.sendToParent('init');
      self.messenger.once('initResourceList', function(data) {
        self.serverData = data;
        self.resource = data['resource'];
        document.documentElement.lang = data['context']['lang'];
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

window.ltc = new LtcSDKClient();
