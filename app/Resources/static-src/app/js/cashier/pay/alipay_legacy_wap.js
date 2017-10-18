import BasePayment from './payment';

export default class AlipayLegacyWap extends BasePayment {

  afterTradeCreated(res) {
    location.href = res.payUrl;
  }

  customParams(params) {
    params['app_pay'] = 'Y';
    return params;
  }
}
