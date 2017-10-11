import BasePayment from './payment';

export default class WechatPayJs extends BasePayment {

  afterTradeCreated(res) {
    location.href = '/pay/center/wxpay?tradeSn=' + res.tradeSn;
  }
}
