
import { init } from 'test/util/init.js';
const assert = require('chai').assert;
const sinon = require('sinon');
import Api from 'common/api';

let basePament, BasePament;
describe('pay:getTrade', function() {
  before(function() {
    init('');
    BasePament = require('test-src/app/js/cashier/pay/payment.js').default;
    basePament = new BasePament();
  });

  it('function:getTrade when trade is empty', function() {
    BasePament.getTrade('').then(res => {
      assert.equal(res.isPaid, false);
    });
  });

  it('function:getTrade when tradesn not empty and ordersn not empty', function() {
    let apiTradeGet = sinon.stub(Api.trade, 'get');
    let expectedParams = {params: { orderSn: '201802123', tradeSn: '20180202' }};
    BasePament.getTrade('20180202', '201802123');
    apiTradeGet.restore();
    sinon.assert.calledWith(apiTradeGet, expectedParams);
  });

  it('function:getTrade when tradesn is not empty and ordersn is empty', function() {
    let apiTradeGet = sinon.stub(Api.trade, 'get');
    let expectedParams = {params: {tradeSn: '12431234' }};
    BasePament.getTrade('12431234');
    apiTradeGet.restore();
    sinon.assert.calledWith(apiTradeGet, expectedParams);
  });
});

describe('pay:getTrade', function() {
  before(function() {
    init('');
    BasePament = require('test-src/app/js/cashier/pay/payment.js').default;
    basePament = new BasePament();
  });
  it('function:startInterval', function() {
    assert.equal(basePament.startInterval(), false);
  });
});