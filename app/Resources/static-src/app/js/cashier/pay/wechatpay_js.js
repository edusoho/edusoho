import BasePayment from './payment';

export default class WechatPayJs extends BasePayment {

  pay(res) {
    location.href = '/pay/center/wxpay?' + $.param(res);
  }
}
