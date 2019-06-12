import Api from 'common/api';
import 'store';
import notify from 'common/notify';
import ConfirmModal from './confirm';
require('app/common/xxtea.js');

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
    if (trade == null) {
      return;
    }
    if (trade.paidSuccessUrl) {
      location.href = trade.paidSuccessUrl;
    } else {
      store.set('trade_' + this.getURLParameter('sn'), trade.tradeSn);
      this.afterTradeCreated(trade);
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
    clearInterval(window.intervalCheckOrderId);
  }

  startInterval() {
    return false;
  }

  checkIsPaid() {
    let tradeSn = store.get('trade_' + this.getURLParameter('sn'));
    BasePayment.getTrade(tradeSn).then(res => {
      if (res.isPaid) {
        store.remove('payment_gateway');
        store.remove('trade_' + this.getURLParameter('sn'));
        location.href = res.paidSuccessUrl;
      }
    });
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
      payPassword: window.XXTEA.encryptToBase64(postParams.payPassword, 'EduSoho'),
    };

    params = this.customParams(params);

    Object.keys(params).forEach(k => (!params[k] && params[k] !== undefined) && delete params[k]);

    return params;
  }

  createTrade(postParams) {

    let params = this.filterParams(postParams);

    let trade = null;

    Api.trade.create({ data: params, async: false, promise: false }).done(res => {
      trade = res;
    }).error(res => {
      let response = JSON.parse(res.responseText);
      if (response.error.code == 2) {
        notify('danger', response.error.message);
      } else {
        notify('danger', Translator.trans('cashier.pay.error_message'));
      }
    });

    return trade;
  }

  static getTrade(tradeSn, orderSn = '') {
    let params = {};

    if (tradeSn == undefined || tradeSn == '') {
      return new Promise((resolve, reject) => {
        resolve({ isPaid: false });
      });
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