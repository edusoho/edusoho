import BasePayment from './payment';

export default class WechatPayMweb extends BasePayment {

  afterTradeCreated(res) {
    this.checkOrderStatus();
    location.href = res.mwebUrl;
  }

  startInterval() {
    return true;
  }
}
