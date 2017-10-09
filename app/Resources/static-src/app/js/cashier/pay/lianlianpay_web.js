import BasePayment from './payment';

export default class LianlianpayWeb extends BasePayment {

  afterTradeCreated(res) {
    window.open(res.redirectUrl);
    this.showConfirmModal(res.tradeSn);
  }

}
