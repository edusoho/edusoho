
import { getRootPath, init } from '../../../util/init.js';
import decache from 'decache';
const assert = require('chai').assert;

init('');
let BasePament = require(getRootPath() + '/app/Resources/static-src/app/js/cashier/pay/payment.js').default;
let basePament = new BasePament();

describe('pay:payment', function() {
  before(function() {

  });

  after(function() {
    decache('../../../util/init.js');
  });

  it('function:getTrade', function(done) {
    // BasePament.getTrade('').then(res => {
    //   assert.equal(res.isPaid, false);
    // });
    // console.log(BasePament.getTrade('baidu.com'));
    $.post('/', function(e){
      console.log(e);
      done();
    })
  });

  it('function:startInterval', function() {
    assert.equal(basePament.startInterval(), false);
  });
});