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
    let trade = this.createTrade(params);
    if (trade.paidSuccessUrl) {
      location.href = trade.paidSuccessUrl;
    } else {
      this.afterTradeCreated(trade)
    }

  }

  afterTradeCreated(res) {

  }

  customParams(params) {
    return params;
  }

  checkOrderStatus() {
    if (this.startInterval()) {
      window.intervalCheckOrderId = setInterval(this.checkIsPaid.bind(this), 2000);
    }
  }

  cancelCheckOrder() {
    clearInterval(window.intervalCheckOrderId)
  }

  startInterval() {
    return false;
  }

  checkIsPaid() {
    let tradeSn = store.get('trade_' + this.getURLParameter('sn'));
    BasePayment.getTrade(tradeSn).then(res => {
      if (res.isPaid) {
        location.href = res.paidSuccessUrl;
      }
    })
  }

  getURLParameter(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null;
  }

  filterParams(postParams) {
    let params = {
      gateway: postParams.gateway,
      type: postParams.type,
      orderSn: postParams.orderSn,
      coinAmount: postParams.coinAmount,
      amount: postParams.amount,
      openid: postParams.openid,
      payPassword: postParams.payPassword
    };

    params = this.customParams(params);

    Object.keys(params).forEach(k => (!params[k] && params[k] !== undefined) && delete params[k]);

    return params;
  }

  createTrade(postParams) {

    let params = this.filterParams(postParams);

    let trade = null;

    Api.trade.create({data: params, async: false, promise: false}).done(res => {
      trade = res;
    }).error(res => {
      notify('danger', Translator.trans('cashier.pay.error_message'));
    });

    return trade;
  }

  static getTrade(tradeSn, orderSn = '') {
    let params = {};

    if (tradeSn == undefined || tradeSn == '') {
      return false;
    }
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