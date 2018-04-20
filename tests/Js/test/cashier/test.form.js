import { init } from 'test/util/init.js';
import Coin from 'test-src/app/js/cashier/coin';
import PaySdk from 'test-src/app/js/cashier/pay/sdk.js';
import CashierForm from 'test-src/app/js/cashier/form';
const assert = require('chai').assert;
const sinon = require('sinon');

let cashierForm, paySdk;
describe('app/js/cashier/form:constructor', function() {
  it('function:constructor', function() {
    paySdk = new PaySdk();
  });
});

describe('app/js/cashier/form:initCoin', function() {
  it('function:initCoin', function() {
    init('<div id="coin-use-section"></div>', { url: 'http://try6.edusoho.cn/'});
    cashierForm = new CashierForm({ element: '#cashier-form' });
    let $coin = $('#coin-use-section');
    const coin = new Coin({
      $coinContainer: $coin,
      cashierForm: cashierForm,
      $form: $('#cashier-form')
    });
    console.log(global.window);
    cashierForm.initCoin();
    assert.deepEqual(cashierForm.coin, coin);
  });
});

