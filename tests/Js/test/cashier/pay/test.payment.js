
import { getRootPath, init } from '../../../util/init.js';
import decache from 'decache';
const assert = require('chai').assert;
const sinon = require('sinon');
import Api from 'common/api';

init('');
let BasePament = require(getRootPath() + '/app/Resources/static-src/app/js/cashier/pay/payment.js').default;
let basePament = new BasePament();

describe('pay:payment', function() {
  before(function() {

  });

  after(function() {
    decache('../../../util/init.js');
  });

  it('function:getTrade when trade is empty', function() {
    BasePament.getTrade('').then(res => {
      assert.equal(res.isPaid, false);
    });
  });

  it('function:getTrade when tradesn not empty and ordersn not empty', function() {
    var apiTradeGet = sinon.stub(Api.trade, 'get');
    var expectedParams = {params: { orderSn: '201802123', tradeSn: '20180202' }};
    BasePament.getTrade('20180202', '201802123');
    apiTradeGet.restore();
    sinon.assert.calledWith(apiTradeGet, expectedParams);
  });

  it('function:getTrade when tradesn is not empty and ordersn is empty', function() {
    var apiTradeGet = sinon.stub(Api.trade, 'get');
    var expectedParams = {params: {tradeSn: '12431234' }};
    BasePament.getTrade('12431234');
    apiTradeGet.restore();
    sinon.assert.calledWith(apiTradeGet, expectedParams);
  });

  it('function:startInterval', function() {
    assert.equal(basePament.startInterval(), false);
    // var ajax = sinon.stub($, 'ajax');
    // ajax.restore();
    // sinon.assert.calledWith(ajax, expectedParams);
  });
});