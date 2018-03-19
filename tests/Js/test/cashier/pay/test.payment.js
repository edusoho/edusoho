
import { getRootPath, init } from '../../../util/init.js';
import decache from 'decache';
const assert = require('chai').assert;

init('<div></div>');
let BasePament = require(getRootPath() + '/app/Resources/static-src/app/js/cashier/pay/payment.js').default;
let basePament = new BasePament();

describe('pay:payment', function() {
  before(function() {

  });

  after(function() {
    decache('../../../util/init.js');
  });

  it('function:getTrade', function() {
    BasePament.getTrade('123', '123');
  });

  it('function:startInterval', function() {
    assert.equal(basePament.startInterval(), false);
  });
});