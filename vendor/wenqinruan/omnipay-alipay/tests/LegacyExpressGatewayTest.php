<?php

namespace Omnipay\Alipay\Tests;

use Omnipay\Alipay\Common\Signer;
use Omnipay\Alipay\LegacyExpressGateway;
use Omnipay\Alipay\Responses\LegacyExpressPurchaseResponse;
use Omnipay\Alipay\Responses\LegacyQueryResponse;
use Omnipay\Alipay\Responses\LegacyRefundResponse;

class LegacyExpressGatewayTest extends AbstractGatewayTestCase
{

    /**
     * @var LegacyExpressGateway $gateway
     */
    protected $gateway;

    protected $options;


    public function setUp()
    {
        parent::setUp();
        $this->gateway = new LegacyExpressGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setPartner($this->partner);
        $this->gateway->setKey($this->key);
        $this->gateway->setSellerId($this->sellerId);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');
        $this->gateway->setReturnUrl('https://www.example.com/return');
        $this->options = [
            'out_trade_no' => '2014010122390001',
            'subject'      => 'test',
            'total_fee'    => '0.01',
        ];
    }


    public function testPurchase()
    {
        /**
         * @var LegacyExpressPurchaseResponse $response
         */
        $response = $this->gateway->purchase($this->options)->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($response->getRedirectUrl());
    }


    public function testRefund()
    {
        /**
         * @var LegacyRefundResponse $response
         */
        $response = $this->gateway->refund(
            [
                'refund_items' => [
                    [
                        'out_trade_no' => '2016092021001003280286716852',
                        'amount'       => '1',
                        'reason'       => 'test',
                    ]
                ]
            ]
        )->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($response->getRedirectUrl());
    }


    public function testQuery()
    {
        /**
         * @var LegacyQueryResponse $response
         */
        $response = $this->gateway->query(
            [
                'out_trade_no' => '2016092021001003280286716850'
            ]
        )->send();

        $this->assertFalse($response->isSuccessful());
    }


    public function testCompletePurchaseWithMD5()
    {
        $str = 'buyer_email=aaa%40qq.com&buyer_id=2088202561123456&exterface=create_direct_pay_by_user&is_success=T&notify_id=RqPnCoPT3K9%252Fvwbh3InWes5p8ZRYIdWn4DYfTZV%252FByZc5wcE2q9pffj29yQCHA%252BTNHUY&notify_time=2016-09-24+21%3A03%3A57&notify_type=trade_status_sync&out_trade_no=2016-09-24+15%3A03%3A078138&payment_type=1&seller_email=test%40qq.com&seller_id=20880114664123456&subject=test&total_fee=0.01&trade_no=201609242100100406123456789&trade_status=TRADE_SUCCESS&sign=ea00ba288bf6e1cd4a6e89c5f180df7d&sign_type=MD5';

        parse_str($str, $data);

        $data['sign']      = (new Signer($data))->signWithMD5($this->key);
        $data['sign_type'] = 'MD5';

        $this->gateway = new LegacyExpressGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setPartner($this->partner);
        $this->gateway->setKey($this->key);
        $this->gateway->setSignType('RSA');
        $this->gateway->setPrivateKey(ALIPAY_LEGACY_PRIVATE_KEY);
        $this->gateway->setSellerId($this->sellerId);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');
        $this->gateway->setReturnUrl('https://www.example.com/return');

        /**
         * @var LegacyExpressPurchaseResponse $response
         */
        $request = $this->gateway->completePurchase(['params' => $data]);
        $request->setVerifyNotifyId(false);
        $response = $request->send();
        $this->assertTrue($response->isSuccessful());
    }


    public function testCompletePurchaseWithRSA()
    {
        $testPrivateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';
        $testPublicKey  = ALIPAY_ASSET_DIR . '/dist/common/rsa_public_key.pem';

        $str = 'buyer_email=aaa%40qq.com&buyer_id=2088202561123456&exterface=create_direct_pay_by_user&is_success=T&notify_id=RqPnCoPT3K9%252Fvwbh3InWes5p8ZRYIdWn4DYfTZV%252FByZc5wcE2q9pffj29yQCHA%252BTNHUY&notify_time=2016-09-24+21%3A03%3A57&notify_type=trade_status_sync&out_trade_no=2016-09-24+15%3A03%3A078138&payment_type=1&seller_email=test%40qq.com&seller_id=20880114664123456&subject=test&total_fee=0.01&trade_no=201609242100100406123456789&trade_status=TRADE_SUCCESS&sign=ea00ba288bf6e1cd4a6e89c5f180df7d&sign_type=MD5';

        parse_str($str, $data);

        $data['sign']      = (new Signer($data))->signWithRSA($testPrivateKey);
        $data['sign_type'] = 'RSA';

        $this->gateway = new LegacyExpressGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setPartner($this->partner);
        $this->gateway->setKey($this->key);
        $this->gateway->setSignType('RSA');
        $this->gateway->setPrivateKey($testPrivateKey);
        $this->gateway->setAlipayPublicKey($testPublicKey);
        $this->gateway->setSellerId($this->sellerId);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');
        $this->gateway->setReturnUrl('https://www.example.com/return');

        /**
         * @var LegacyExpressPurchaseResponse $response
         */
        $request = $this->gateway->completePurchase(['params' => $data]);
        $request->setVerifyNotifyId(false);
        $response = $request->send();
        $this->assertTrue($response->isSuccessful());
    }
}
