import BasePayment from './payment';

export default class AlipayLegacyExpress extends BasePayment {

  afterTradeCreated(res) {
    let options = this.getOptions();
    if (options.showConfirmModal) {
      window.open(res.redirectUrl);
      this.showConfirmModal(res.tradeSn);
    } else {
      location.href = res.redirectUrl;
    }

  }

}
