// import 'babel-polyfill';
// import 'jquery';
// import * as cd from 'codeages-design';
// import '!style-loader!css-loader!less-loader!codeages-design/src/less/codeages-design.less';
import Api from './api';
import * as components from './component';
import EsMessenger from 'app/common/messenger';

class LtcSDKClient {
  constructor() {
    this.options = {};
    this.handler = {};
    this.isVerify = false;
  }

  passport() {
    // 需替换成真实的验证机制
    // if (this.options.appId == '123456') {
    //   console.log('验证成功');
    // } else {
    //   throw new Error('验证身份失败');
    // }
  
    if (this.options.apiList instanceof Array) {
      this.options.apiList.forEach((item) => {
        if (!this[item]) {
          throw new Error('不存在 ' + item + ' 接口');
        }
      });
    }

    this.isVerify = true;
  }

  verify() {
    if (!this.isVerify) {
      throw new Error('请先调用config方法，验证身份');
    }
  }

  getMessenger() {

    let messenger = new EsMessenger({
      name: 'partner',
      project: 'LtcProject',
      children: [],
      type: 'child'
    });
    return messenger;
  }

  config(options) {
    let DEFAULTS = {
      apiList: [],
      appId: null,
    }

    Object.assign(this.options, DEFAULTS, options);
  
    this.passport();
  
    return this;
  }

  getApi(options) {
    this.verify();
    return Api(options);
  }

  getUi() {
    this.verify();
    return Object.assign(
      {}, 
      // cd, 
      components
    );
  }
}

let ltcsdk = new LtcSDKClient();

module.exports = window.ltcsdkclient = ltcsdk;