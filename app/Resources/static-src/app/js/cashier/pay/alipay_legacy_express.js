import BasePayment from './payment';

export default class AlipayLegacyExpress extends BasePayment {

  beforeCreateTrade() {
    let options = this.getOptions();
    if (options.showConfirmModal) {
      this.newWindow = window.open('/cashier/redirect', '_blank');
    }

  }

  afterTradeCreated(res) {
    let options = this.getOptions();
    if (options.showConfirmModal) {
      this.newWindow.location.href = res.payUrl;
      this.showConfirmModal(res.tradeSn);
    } else {
      location.href = res.payUrl;
    }

  }

}
