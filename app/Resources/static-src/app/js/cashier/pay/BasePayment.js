import Api from 'common/api';
import notify from 'common/notify';

export default class BasePayment {

  constructor() {

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

  static getTrade(tradeSn, callback) {
    let params = {
      tradeSn: tradeSn
    };

    Api.trade.get({
      params: params
    }).then(callback);
  }
}