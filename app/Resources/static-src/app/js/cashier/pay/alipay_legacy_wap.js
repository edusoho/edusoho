import BasePayment from './payment';

export default class AlipayLegacyWap extends BasePayment {

  afterTradeCreated(res) {
    location.href = res.redirectUrl;
  }

}
