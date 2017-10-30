import BasePayment from './payment';

export default class AlipayLegacyExpress extends BasePayment {

  afterTradeCreated(res) {
    this.checkOrderStatus();
    let options = this.getOptions();
    if (options.showConfirmModal) {
      window.open(res.payUrl, '_blank');
      this.showConfirmModal(res.tradeSn);
    } else {
      location.href = res.payUrl;
    }

  }

}