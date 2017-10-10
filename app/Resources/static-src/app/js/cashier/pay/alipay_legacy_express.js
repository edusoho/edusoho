import BasePayment from './payment';

export default class AlipayLegacyExpress extends BasePayment {

  afterTradeCreated(res) {
    window.open(res.redirectUrl);
    // let newTab = window.open('about:blank');
    // newTab.location.href = res.redirectUrl;
    this.showConfirmModal(res.tradeSn);
  }

}
