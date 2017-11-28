<?php

namespace Omnipay\Alipay\Tests;

use Omnipay\Alipay\Common\Signer;
use Omnipay\Alipay\LegacyAppGateway;
use Omnipay\Alipay\Responses\LegacyAppPurchaseResponse;
use Omnipay\Alipay\Responses\LegacyCompletePurchaseResponse;

class LegacyAppGatewayTest extends AbstractGatewayTestCase
{

    /**
     * @var LegacyAppGateway $gateway
     */
    protected $gateway;

    protected $options;


    public function setUp()
    {
        parent::setUp();

        $this->gateway = new LegacyAppGateway($this->getHttpClient(), $this->getHttpRequest());
    }


    public function testCreateOrder()
    {
        $partner    = '123456789';
        $privateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';

        //$partner    = ALIPAY_PARTNER;
        //$privateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';

        $this->assertFileExists($privateKey);

        $this->gateway = new LegacyAppGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setPartner($partner);
        $this->gateway->setSellerId($partner);
        $this->gateway->setPrivateKey($privateKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');

        $this->options = [
            'out_trade_no' => '2014010122390001',
            //'out_trade_no' => date('YmdHis').mt_rand(1000,9999),
            'subject'      => 'test',
            'total_fee'    => '0.01',
        ];

        /**
         * @var LegacyAppPurchaseResponse $response
         */
        $response = $this->gateway->purchase($this->options)->send();

        $this->assertEquals('e16fdd8098c197201986cd9c3a8fb276', md5($response->getOrderString()));
    }


    public function testCompletePurchase()
    {
        $testPrivateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';
        $testPublicKey  = ALIPAY_ASSET_DIR . '/dist/common/rsa_public_key.pem';

        $data = [
            'memo'         => '',
            'result'       => '_input_charset="UTF-8"&alipay_sdk="lokielse/omnipay-alipay"&notify_url="https://www.example.com/notify"&out_trade_no="2016092309184123456"&partner="80123456789"&payment_type="1"&seller_id="80123456789"&service="mobile.securitypay.pay"&subject="test"&total_fee="0.01"&success="true"',
            'resultStatus' => '9000'
        ];

        $sign = (new Signer())->signContentWithRSA($data['result'], $testPrivateKey);

        $data['result'] = sprintf('%s&sign_type="RSA"&sign="%s"', $data['result'], $sign);
        $data['result'] = addslashes($data['result']);

        $partner    = ALIPAY_PARTNER;
        $privateKey = ALIPAY_LEGACY_PRIVATE_KEY;

        $this->assertFileExists($privateKey);

        $this->gateway = new LegacyAppGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setPartner($partner);
        $this->gateway->setSellerId($partner);
        $this->gateway->setPrivateKey($privateKey);
        $this->gateway->setAlipayPublicKey($testPublicKey);

        /**
         * @var LegacyCompletePurchaseResponse $response
         */
        $response = $this->gateway->completePurchase(['params' => $data])->send();

        $this->assertEquals('2016092309184123456', $response->data('out_trade_no'));
        $this->assertEquals(
            '{"_input_charset":"UTF-8","alipay_sdk":"lokielse\/omnipay-alipay","notify_url":"https:\/\/www.example.com\/notify","out_trade_no":"2016092309184123456","partner":"80123456789","payment_type":"1","seller_id":"80123456789","service":"mobile.securitypay.pay","subject":"test","total_fee":"0.01","success":"true","sign_type":"S","sign":"h9OL+y\/Q1lvUOAklr6gbN8YcDxBiAWYlopO49KlVLKcclfPULzUU9\/+KPWN\/SixyZDFq0wPt6KK8beEvN0YhPbATRwAQvE9ggiBJpW\/FTkE9urxy50LRtFogJCQ+E0TCHkbNRHpLhXnbzjth5R5gms\/u0rdnn5ALMBL1r7c\/5s","trade_status":"TRADE_SUCCESS"}',
            json_encode($response->getData())
        );
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isPaid());
    }
}
