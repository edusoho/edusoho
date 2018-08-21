import BasePayment from './payment';

export default class AlipayLegacyWap extends BasePayment {

  afterTradeCreated(res) {
    location.href = res.payUrl;
  }

  customParams(params) {
    if (!this.isQQBuildInBrowser()) {
      params['app_pay'] = 'Y';
      params['wap_pay'] = true;
    }
    return params;
  }

  isQQBuildInBrowser() {
    return navigator.userAgent.match(/QQ\//i) ? true : false;
  }
}