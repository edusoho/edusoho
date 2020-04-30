import BasePayment from './payment';

export default class CoinPay extends BasePayment {

  afterTradeCreated(res) {
    location.href = res.payUrl;
  }

}
