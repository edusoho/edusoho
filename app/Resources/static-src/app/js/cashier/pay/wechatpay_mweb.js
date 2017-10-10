import BasePayment from './payment';

export default class WechatPayMweb extends BasePayment {

  afterTradeCreated(res) {
    location.href = '/pay/center/wxpay?tradeSn=' + res.tradeSn;
  }
}
