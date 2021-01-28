<?php

namespace Omnipay\Alipay\Tests;

use Omnipay\Alipay\AopWapGateway;
use Omnipay\Alipay\Common\Signer;
use Omnipay\Alipay\Responses\AopCompletePurchaseResponse;
use Omnipay\Alipay\Responses\AopCompleteRefundResponse;
use Omnipay\Alipay\Responses\AopTradeWapPayResponse;

class AopWapGatewayTest extends AbstractGatewayTestCase
{

    /**
     * @var AopWapGateway $gateway
     */
    protected $gateway;

    protected $options;


    public function setUp()
    {
        parent::setUp();
        $this->gateway = new AopWapGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey($this->appPrivateKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');
        $this->gateway->setReturnUrl('https://www.example.com/return');
    }


    public function testPurchase()
    {
        /**
         * @var AopTradeWapPayResponse $response
         */
        $response = $this->gateway->purchase(
            [
                'biz_content' => [
                    'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
                    'total_amount' => 0.01,
                    'subject'      => 'test',
                    'product_code' => 'QUICK_MSECURITY_PAY',
                ]
            ]
        )->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($response->getRedirectUrl());
    }


    public function testCompletePurchase()
    {
        $testPrivateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';
        $testPublicKey  = ALIPAY_ASSET_DIR . '/dist/common/rsa_public_key.pem';

        $this->gateway = new AopWapGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey($this->appPrivateKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');
        $this->gateway->setReturnUrl('https://www.example.com/return');

        $str = 'total_amount=0.01&timestamp=2016-09-23+18%3A21%3A58&trade_no=201609232100100306012345678&auth_app_id=201511280012345678&charset=UTF-8&seller_id=20880114612345678&method=alipay.trade.wap.pay.return&app_id=20151128001234567&out_trade_no=201609231211234567&version=1.0';

        parse_str($str, $data);

        $data['sign']      = (new Signer($data))->signWithRSA($testPrivateKey);
        $data['sign_type'] = 'RSA';

        $this->gateway->setAlipayPublicKey($testPublicKey);

        /**
         * @var AopCompletePurchaseResponse $response
         */
        $response = $this->gateway->completePurchase(['params' => $data])->send();

        $this->assertEquals(
            '{"total_amount":"0.01","timestamp":"2016-09-23 18:21:58","trade_no":"201609232100100306012345678","auth_app_id":"201511280012345678","charset":"UTF-8","seller_id":"20880114612345678","method":"alipay.trade.wap.pay.return","app_id":"20151128001234567","out_trade_no":"201609231211234567","version":"1.0","sign":"ZuYCQRwbU50H2x1qevu0ZEFKwTE1piXpBG7GATUh3AZXF3S7CZ07Jj+mVDoa6WrOCGFfA8lHSbE28RX\/pl5QGxRuT+8B4KVo\/NWm3R10NCgqhkvBB+qPfUMSaBgaM+RR5m647QiKROmzX8sd4IgcedIZNKGicem+DJwNPayTLug=","sign_type":"RSA","trade_status":null}',
            json_encode($response->data())
        );

        $this->assertEquals('201609231211234567', $response->data('out_trade_no'));
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isPaid());
    }

    public function testCompleteRefund()
    {
        $testPrivateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';
        $testPublicKey  = ALIPAY_ASSET_DIR . '/dist/common/rsa_public_key.pem';

        $this->gateway = new AopWapGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey($this->appPrivateKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');
        $this->gateway->setReturnUrl('https://www.example.com/return');

        $str = 'total_amount=0.01&timestamp=2016-09-23+18%3A21%3A58&trade_no=201609232100100306012345678&auth_app_id=201511280012345678&charset=UTF-8&seller_id=20880114612345678&method=alipay.trade.wap.pay.return&app_id=20151128001234567&out_trade_no=201609231211234567&version=1.0';

        parse_str($str, $data);

        $data['sign']      = (new Signer($data))->signWithRSA($testPrivateKey);
        $data['sign_type'] = 'RSA';

        $this->gateway->setAlipayPublicKey($testPublicKey);

        /**
         * @var AopCompleteRefundResponse $response
         */
        $response = $this->gateway->completeRefund(['params' => $data])->send();

        $this->assertEquals(
            '{"total_amount":"0.01","timestamp":"2016-09-23 18:21:58","trade_no":"201609232100100306012345678","auth_app_id":"201511280012345678","charset":"UTF-8","seller_id":"20880114612345678","method":"alipay.trade.wap.pay.return","app_id":"20151128001234567","out_trade_no":"201609231211234567","version":"1.0","sign":"ZuYCQRwbU50H2x1qevu0ZEFKwTE1piXpBG7GATUh3AZXF3S7CZ07Jj+mVDoa6WrOCGFfA8lHSbE28RX\/pl5QGxRuT+8B4KVo\/NWm3R10NCgqhkvBB+qPfUMSaBgaM+RR5m647QiKROmzX8sd4IgcedIZNKGicem+DJwNPayTLug=","sign_type":"RSA","trade_status":null}',
            json_encode($response->data())
        );

        $this->assertEquals('201609231211234567', $response->data('out_trade_no'));
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRefunded());
    }
}
