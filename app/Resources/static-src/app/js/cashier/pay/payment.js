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

    this.beforeCreateTrade();

    BasePayment.createTrade(params, this.afterTradeCreated.bind(this));
  }

  beforeCreateTrade() {

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

  static createTrade(postParams, callback) {

    let params = this.filterParams(postParams);

    Api.trade.create({data:params}).then(res => {
      if (res.paidSuccessUrl) {
        location.href = res.paidSuccessUrl;
      } else {
        callback(res)
      }

    }).catch(res => {
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