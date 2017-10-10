import Api from 'common/api';
import notify from 'common/notify';
import ConfirmModal from './confirm';

export default class BasePayment {

  setOptions(options) {
    this.options = options;
  }

  getOptions() {
    return this.options;
  }

  showConfirmModal(tradeSn) {
    if (!this.confirmModal) {
      this.confirmModal = new ConfirmModal();
    }

    this.confirmModal.show(tradeSn);
  }

  pay(params) {

    let trade = BasePayment.createTrade(params);
    if (trade.paidSuccessUrl) {
      location.href = trade.paidSuccessUrl;
    } else {
      this.afterTradeCreated(trade)
    }

  }

  afterTradeCreated(res) {

  }

  static filterParams(postParams) {
    let params = {
      gateway: postParams.gateway,
      type: postParams.type,
      orderSn: postParams.orderSn,
      coinAmount: postParams.coinAmount,
      amount: postParams.amount,
      openid: postParams.openid,
      payPassword: postParams.payPassword
    };

    Object.keys(params).forEach(k => (!params[k] && params[k] !== undefined) && delete params[k]);

    return params;
  }

  static createTrade(postParams) {

    let params = this.filterParams(postParams);

    let trade = null;

    Api.trade.create({data:params, async: false, promise: false}).done( res => {
      trade = res;
    }).error( res => {
      notify('danger', Translator.trans('cashier.pay.error_message'));
    });

    return trade;
  }

  static getTrade(tradeSn, orderSn = '') {
    let params = {};

    if (tradeSn) {
      params.tradeSn = tradeSn;
    }

    if (orderSn) {
      params.orderSn = orderSn;
    }

    return Api.trade.get({
      params: params
    });
  }
}