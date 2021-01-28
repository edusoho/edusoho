<?php

namespace Omnipay\Alipay\Tests;

use Omnipay\Alipay\Common\Signer;
use Omnipay\Alipay\LegacyWapGateway;
use Omnipay\Alipay\Requests\LegacyCompletePurchaseRequest;
use Omnipay\Alipay\Responses\LegacyCompletePurchaseResponse;
use Omnipay\Alipay\Responses\LegacyWapPurchaseResponse;

class LegacyWapGatewayTest extends AbstractGatewayTestCase
{

    /**
     * @var LegacyWapGateway $gateway
     */
    protected $gateway;

    protected $options;


    public function setUp()
    {
        parent::setUp();
        $this->gateway = new LegacyWapGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setPartner($this->partner);
        $this->gateway->setKey($this->key);
        $this->gateway->setSellerId($this->sellerId);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');
        $this->gateway->setReturnUrl('https://www.example.com/return');
        $this->options = [
            'out_trade_no' => '2014010122390001',
            'subject'      => 'test',
            'total_fee'    => '0.01',
            'show_url'     => 'https://www.example.com/item/123456',
        ];
    }


    public function testPurchase()
    {
        /**
         * @var LegacyWapPurchaseResponse $response
         */
        $response = $this->gateway->purchase($this->options)->send();

        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->isSuccessful());
        $this->assertNotEmpty($response->getRedirectUrl());
    }


    public function testCompletePurchase()
    {
        $testPrivateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';
        $testPublicKey  = ALIPAY_ASSET_DIR . '/dist/common/rsa_public_key.pem';

        $str = 'is_success=T&notify_id=RqPoCoPT3K9%2s2Fvwbh3InWes%253Fe6fSMGAUrCZUnt1LaaMPvSzYGULQLwqktj%252Fy9nV2iA2lV&notify_time=2016-09-23+14%3A59%3A33&notify_type=trade_status_sync&out_trade_no=201609230859157269&payment_type=1&seller_id=20880114664123456&service=alipay.wap.create.direct.pay.by.user&subject=test&total_fee=0.01&trade_no=201609232100100306021123456&trade_status=TRADE_FINISHED';

        parse_str($str, $data);

        $data['sign']      = (new Signer($data))->signWithRSA($testPrivateKey);
        $data['sign_type'] = 'RSA';

        $partner    = ALIPAY_PARTNER;
        $privateKey = ALIPAY_LEGACY_PRIVATE_KEY;

        $this->assertFileExists($privateKey);

        $this->gateway = new LegacyWapGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setPartner($partner);
        $this->gateway->setSellerId($partner);
        $this->gateway->setPrivateKey($privateKey);
        $this->gateway->setAlipayPublicKey($testPublicKey);

        /**
         * @var LegacyCompletePurchaseRequest  $request
         * @var LegacyCompletePurchaseResponse $response
         */
        $request = $this->gateway->completePurchase(['params' => $data]);
        $request->setVerifyNotifyId(false);
        $response = $request->send();

        $this->assertEquals(
            '{"is_success":"T","notify_id":"RqPoCoPT3K9%2s2Fvwbh3InWes%3Fe6fSMGAUrCZUnt1LaaMPvSzYGULQLwqktj%2Fy9nV2iA2lV","notify_time":"2016-09-23 14:59:33","notify_type":"trade_status_sync","out_trade_no":"201609230859157269","payment_type":"1","seller_id":"20880114664123456","service":"alipay.wap.create.direct.pay.by.user","subject":"test","total_fee":"0.01","trade_no":"201609232100100306021123456","trade_status":"TRADE_FINISHED","sign":"LFlQYg\/VoS6y1NWXgvfJ+FEs5xutTt8thBgwIfxesqFhFL8agPGYz6TyzDe+oPNHgqdwH+HuB+kQOgnMVD5QCOP4DAgO72RGKNhJMwLMMNCfpcVrB4D0tBXkacSCj1xxixsIzLVlIftefkOUbEpOVHwmb1FwYkuJfrhINbRq6oI=","sign_type":"RSA"}',
            json_encode($response->data())
        );

        $this->assertEquals('201609230859157269', $response->data('out_trade_no'));
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isPaid());
    }
}
