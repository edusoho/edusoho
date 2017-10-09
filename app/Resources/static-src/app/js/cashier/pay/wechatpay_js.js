import BasePayment from './payment';

export default class WechatPayJs extends BasePayment {

  successUrl = '';
  jsApiParams = null;

  pay(params) {
    params = BasePayment.filterParams(params);
    params.s = 1;
    location.href = '/pay/center/wxpay?' + $.param(params);
  }

  callback(res) {
    this.successUrl = res.successUrl;
    this.jsApiParams = res.jsApiParams;

    if (typeof WeixinJSBridge === "undefined") {
      if (document.addEventListener) {
        document.addEventListener('WeixinJSBridgeReady', this.jsApiCall.bind(this), false);
      } else if (document.attachEvent) {
        document.attachEvent('WeixinJSBridgeReady', this.jsApiCall.bind(this));
        document.attachEvent('onWeixinJSBridgeReady', this.jsApiCall.bind(this));
      }
    } else {
      jsApiCall().bind(this);
    }
  }

  jsApiCall() {
    WeixinJSBridge.invoke(
      'getBrandWCPayRequest',
      this.jsApiParams, this.wechatPayCallback.bind(this)
    );
  }

  wechatPayCallback(res) {
    if (res.err_msg === 'get_brand_wcpay_request:ok') {
      window.location.href = this.successUrl;
    } else {
      if (res.err_msg === 'get_brand_wcpay_request:fail') {
        alert(Translator.trans('notify.pay_failed.message'));
      } else if (res.err_msg === 'get_brand_wcpay_request:cancel') {
      }
    }
  }
}
