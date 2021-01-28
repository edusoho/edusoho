import { init } from 'test/util/init.js';
import WechatPayNative from 'test-src/app/js/cashier/pay/wechatpay_native';
import AlipayLegacyExpress from 'test-src/app/js/cashier/pay/alipay_legacy_express';
import AlipayLegacyWap from 'test-src/app/js/cashier/pay/alipay_legacy_wap';
import LianlianpayWap from 'test-src/app/js/cashier/pay/lianlianpay_wap';
import LianlianpayWeb from 'test-src/app/js/cashier/pay/lianlianpay_web';
import WechatPayJs from 'test-src/app/js/cashier/pay/wechatpay_js';
import WechatPayMweb from 'test-src/app/js/cashier/pay/wechatpay_mweb';
import PaySdk from 'test-src/app/js/cashier/pay/sdk.js';
const assert = require('chai').assert;
const sinon = require('sinon');
import decache from 'decache';
let paySdk, storeSinon;

describe('app/js/cashier/pay/sdk:checkOrderStatus', function() {
  it('payment is alipay', function() {
    paySdk =  new PaySdk();
    let sinonPaySpy = sinon.spy();
    let sinonPaySdk = sinon.stub(paySdk, 'initPaySdk').returns({checkOrderStatus: sinonPaySpy});
    paySdk = paySdk.checkOrderStatus();
    assert(sinonPaySpy.calledOnce);
    sinonPaySdk.restore();
  });
});

describe('app/js/cashier/pay/sdk:cancelCheckOrder', function() {
  it('payment is alipay', function() {
    paySdk =  new PaySdk();
    let sinonPaySpy = sinon.spy();
    let sinonPaySdk = sinon.stub(paySdk, 'initPaySdk').returns({cancelCheckOrder: sinonPaySpy});
    paySdk = paySdk.cancelCheckOrder();
    assert(sinonPaySpy.calledOnce);
    sinonPaySdk.restore();
  });
});

describe('app/js/cashier/pay/sdk:pay', function() {
  it('payment is alipay', function() {
    paySdk =  new PaySdk();
    let sinonPaySpy = sinon.spy();
    let sinonPaySdk = sinon.stub(paySdk, 'initPaySdk').returns({pay: sinonPaySpy});
    paySdk = paySdk.pay(
      {payment: 'alipay', isMobile: true, isWechat: true}, {test: 'test'}
    );
    assert.deepEqual(paySdk.options, { showConfirmModal: 1, test: 'test' });
    assert(sinonPaySpy.calledOnce);
    sinon.assert.calledWith(sinonPaySpy, { gateway: "Alipay_LegacyWap", isMobile: true, isWechat: true, payment: "alipay" });
    sinonPaySdk.restore();
  });
});

describe('app/js/cashier/pay/sdk:initPaySdk', function() {
  afterEach(function() {
    if (storeSinon) {
      storeSinon.restore();
    }
  })

  beforeEach(function() {
  });

  it('gateway is undefined and payment_gateway is null', function() {
    paySdk =  new PaySdk();
    let paySdk = paySdk.initPaySdk();
    assert.equal(paySdk, null);
  });

  it('gateway is undefined and payment_gateway is test', function() {
    storeSinon = sinon.stub(store, 'get').returns('test');
    paySdk =  new PaySdk();
    let paySdk = paySdk.initPaySdk();
    sinon.assert.calledWith(storeSinon, 'payment_gateway');
    assert.equal(paySdk, null);
  });

  it('gateway is undefined and payment_gateway is WechatPay_Native', function() {
    storeSinon = sinon.stub(store, 'get').returns('WechatPay_Native');
    paySdk =  new PaySdk();
    let paySdk = paySdk.initPaySdk();
    sinon.assert.calledWith(storeSinon, 'payment_gateway');
    assert.isNotFalse(paySdk instanceof WechatPayNative);
  });

  it('gateway is WechatPay_Native', function() {
    paySdk =  new PaySdk();
    let paySdk = paySdk.initPaySdk('WechatPay_Native');
    assert.isNotFalse(paySdk instanceof WechatPayNative);
  });

  it('gateway is WechatPay_MWeb', function() {
    paySdk =  new PaySdk();
    let paySdk = paySdk.initPaySdk('WechatPay_MWeb');
    assert.isNotFalse(paySdk instanceof WechatPayMweb);
  });

  it('gateway is WechatPay_Js', function() {
    paySdk =  new PaySdk();
    let paySdk = paySdk.initPaySdk('WechatPay_Js');
    assert.isNotFalse(paySdk instanceof WechatPayJs);
  });

  it('gateway is Alipay_LegacyExpress', function() {
    paySdk =  new PaySdk();
    let paySdk = paySdk.initPaySdk('Alipay_LegacyExpress');
    assert.isNotFalse(paySdk instanceof AlipayLegacyExpress);
  });

  it('gateway is Alipay_LegacyWap', function() {
    paySdk =  new PaySdk();
    let paySdk = paySdk.initPaySdk('Alipay_LegacyWap');
    assert.isNotFalse(paySdk instanceof AlipayLegacyWap);
  });

  it('gateway is Lianlian_Wap', function() {
    paySdk =  new PaySdk();
    let paySdk = paySdk.initPaySdk('Lianlian_Wap');
    assert.isNotFalse(paySdk instanceof LianlianpayWap);
  });

  it('gateway is Lianlian_Web', function() {
    paySdk =  new PaySdk();
    let paySdk = paySdk.initPaySdk('Lianlian_Web');
    assert.isNotFalse(paySdk instanceof LianlianpayWeb);
  });

});

describe('app/js/cashier/pay/sdk:getGateway', function() {
  afterEach(function() {
    storeSinon.restore();
  })
  beforeEach(function() {
    storeSinon = sinon.stub(store, 'set');
  });

  it('payment is wechat', function() {
    paySdk =  new PaySdk();
    let gateway = paySdk.getGateway('wechat', true, true);
    assert.equal(gateway, 'WechatPay_Js');
    sinon.assert.calledWith(storeSinon, 'payment_gateway', 'WechatPay_Js');

    gateway = paySdk.getGateway('wechat', true, false);
    assert.equal(gateway, 'WechatPay_MWeb');
    sinon.assert.calledWith(storeSinon, 'payment_gateway', 'WechatPay_MWeb');

    gateway = paySdk.getGateway('wechat', false, false);
    assert.equal(gateway, 'WechatPay_Native');
    sinon.assert.calledWith(storeSinon, 'payment_gateway', 'WechatPay_Native');
  });

  it('payment is alipay', function() {
    paySdk =  new PaySdk();
    let gateway = paySdk.getGateway('alipay', true, true);
    assert.equal(gateway, 'Alipay_LegacyWap');
    sinon.assert.calledWith(storeSinon, 'payment_gateway', 'Alipay_LegacyWap');
    
    gateway = paySdk.getGateway('alipay', false, true);
    assert.equal(gateway, 'Alipay_LegacyExpress');
    sinon.assert.calledWith(storeSinon, 'payment_gateway', 'Alipay_LegacyExpress');
  });

  it('payment is lianlianpay', function() {
    paySdk =  new PaySdk();
    let gateway = paySdk.getGateway('lianlianpay', true, true);
    assert.equal(gateway, 'Lianlian_Wap');
    sinon.assert.calledWith(storeSinon, 'payment_gateway', 'Lianlian_Wap');

    gateway = paySdk.getGateway('lianlianpay', false, true);
    assert.equal(gateway, 'Lianlian_Web');
    sinon.assert.calledWith(storeSinon, 'payment_gateway', 'Lianlian_Web');
  });
});
