import BasePayment from './payment';

export default class LianlianpayWap extends BasePayment {

  afterTradeCreated(res) {
    location.href = res.redirectUrl;
  }

}
