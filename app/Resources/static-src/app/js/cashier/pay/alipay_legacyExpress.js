import BasePayment from './BasePayment';

export default class AlipayLegacyExpress extends BasePayment {

  pay(params) {
    BasePayment.createTrade(params, this.callback.bind(this));
  }

  callback(res) {

  }
}
