import Api from 'common/api';
import notify from 'common/notify';
import ConfirmModal from './confirm';

export default class BasePayment {

  showConfirmModal(tradeSn) {
    if (!this.confirmModal) {
      this.confirmModal = new ConfirmModal();
    }

    this.confirmModal.show(tradeSn);
  }

  pay(params) {
    BasePayment.createTrade(params, this.afterTradeCreated.bind(this));
  }

  afterTradeCreated() {

  }

  static createTrade(postParams, callback) {

    let params = {
      gateway: postParams.gateway,
      type: postParams.type,
      orderSn: postParams.orderSn,
      coinAmount: postParams.coinAmount,
      amount: postParams.amount,
    };

    Object.keys(params).forEach(k => (!params[k] && params[k] !== undefined) && delete params[k]);

    Api.trade.create({data:params}).then(callback).catch(res => {
      console.log(res);
      notify('danger', Translator.trans('cashier.pay.error_message'));
    });
  }

  static getTrade(tradeSn) {
    let params = {
      tradeSn: tradeSn
    };

    return Api.trade.get({
      params: params
    });
  }
}