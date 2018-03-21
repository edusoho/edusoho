
import { getRootPath, init } from '../../../util/init.js';
import decache from 'decache';
const assert = require('chai').assert;
const sinon = require('sinon');

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

  it('function:getTrade when trade not empty', function() {
    var ajax = sinon.stub($, 'ajax');

    var expectedParams = {
      async: true,
      dataType: "json",
      params: { tradeSn: "20180202" },
      promise: true,
      type: "GET",
      url: "/api/trades/20180202"
    };

    BasePament.getTrade('20180202').then(res => {
    });

    ajax.restore();
    sinon.assert.calledWith(ajax, expectedParams);
  });

  it('function:startInterval', function() {
    assert.equal(basePament.startInterval(), false);
  });
});