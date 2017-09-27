<?php

namespace Omnipay\Alipay\Tests;

use Omnipay\Alipay\AopAppGateway;
use Omnipay\Alipay\Common\Signer;
use Omnipay\Alipay\Responses\AopCompletePurchaseResponse;
use Omnipay\Alipay\Responses\AopTradeAppPayResponse;

class AopAppGatewayTest extends AbstractGatewayTestCase
{

    /**
     * @var AopAppGateway $gateway
     */
    protected $gateway;

    protected $options;


    public function setUp()
    {
        parent::setUp();
        $this->gateway = new AopAppGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey(ALIPAY_AOP_PRIVATE_KEY);
        //$this->gateway->setAlipayPublicKey(file_get_contents($this->appPrivateKey));
        //$this->gateway->setEncryptKey($this->appEncryptKey);
        //$this->gateway->setNotifyUrl('http://www.guoshuzc.com/api/pay/alipay_recharge_notify');
        //$this->gateway->setAlipaySdk('alipay_sdk');
    }


    public function testPurchase()
    {
        /**
         * @var AopTradeAppPayResponse $response
         */
        $response = $this->gateway->purchase(
            [
                'biz_content' => [
                    'subject'      => 'test',
                    'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
                    'total_amount' => '0.01',
                    'product_code' => 'QUICK_MSECURITY_PAY',
                ]
            ]
        )->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNotEmpty($response->getOrderString());
    }


    public function testPurchaseInline()
    {
        $testPrivateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key_inline.pem';

        $this->gateway->setPrivateKey($testPrivateKey);

        /**
         * @var AopTradeAppPayResponse $response
         */
        $response = $this->gateway->purchase(
            [
                'biz_content' => [
                    'subject'      => 'test',
                    'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
                    'total_amount' => '0.01',
                    'product_code' => 'QUICK_MSECURITY_PAY',
                ]
            ]
        )->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNotEmpty($response->getOrderString());
    }


    public function testCompletePurchaseReturn()
    {
        $testPrivateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';
        $testPublicKey  = ALIPAY_ASSET_DIR . '/dist/common/rsa_public_key.pem';

        $this->gateway = new AopAppGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey($this->appPrivateKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');

        $data = [
            'memo'         => '',
            'result'       => '{\"alipay_trade_app_pay_response\":{\"code\":\"10000\",\"msg\":\"Success\",\"app_id\":\"20151128008123456\",\"auth_app_id\":\"20151128008123456\",\"charset\":\"UTF-8\",\"timestamp\":\"2016-09-23 18:32:16\",\"total_amount\":\"0.01\",\"trade_no\":\"2016092321001003060123456789\",\"seller_id\":\"2088011466123456789\",\"out_trade_no\":\"201609231231447556\"},\"sign\":\"Q5n3zKIBzhBobd6Z6mP69ZvaBlVxkWOiti2ZCRBRhfEH8/sCWE89Iev94K++QH8W9Zakn9dXTq2tR0O5UWLS1XXgiSd+vUTMQNksxjddI39MQnbJ1hxEtwP5GcWzxeY9YRjXXJdzgdf/xmRS7uRQWv52cGYStlCNN/dianZmuDk=\",\"sign_type\":\"RSA\"}',
            'resultStatus' => '9000'
        ];

        $result = json_decode(stripslashes($data['result']), true);

        $signer = new Signer($result['alipay_trade_app_pay_response']);
        $signer->setSort(false);
        $signer->setEncodePolicy(Signer::ENCODE_POLICY_JSON);

        $result['sign']      = $signer->signWithRSA($testPrivateKey);
        $result['sign_type'] = 'RSA';

        $result = addslashes(json_encode($result));

        $data['result'] = $result;

        $this->gateway->setAlipayPublicKey($testPublicKey);

        /**
         * @var AopCompletePurchaseResponse $response
         */
        $response = $this->gateway->completePurchase(['params' => $data])->send();

        $this->assertEquals(
            '{"code":"10000","msg":"Success","app_id":"20151128008123456","auth_app_id":"20151128008123456","charset":"UTF-8","timestamp":"2016-09-23 18:32:16","total_amount":"0.01","trade_no":"2016092321001003060123456789","seller_id":"2088011466123456789","out_trade_no":"201609231231447556","sign":"jdl2MwvZLETOGMCrBvFuIHBlg+DUdd3fsuOqZWr78i1MRLoWOYWGoZNionb9hlW\/UwsRJU8D5Su1LgVADpQH9K\/yTjSH6eMQ4uZ+92QLsmeJxWWW2q85Ah36SULKMrJQDoap\/zWAl\/RV56BH8QpzBIPzby9idkt9VCIbIcSTaA0=","sign_type":"RSA"}',
            json_encode($response->data())
        );

        $this->assertEquals('201609231231447556', $response->data('out_trade_no'));
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isPaid());
        $this->assertEquals('2016092321001003060123456789', $response->getData()['trade_no']);
    }


    public function testCompletePurchaseNotify()
    {
        $testPrivateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';
        $testPublicKey  = ALIPAY_ASSET_DIR . '/dist/common/rsa_public_key.pem';

        $this->gateway = new AopAppGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey($this->appPrivateKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');

        $str = '{"total_amount":"0.01","buyer_id":"20882025611234567","trade_no":"201609232100100306021234567","refund_fee":"0.00","notify_time":"2016-09-23 19:12:33","subject":"test","sign_type":"RSA","notify_type":"trade_status_sync","out_trade_no":"2016092313071234567","gmt_close":"2016-09-23 19:08:10","trade_status":"TRADE_FINISHED","gmt_payment":"2016-09-23 19:08:10","sign":"vCAj0n6vUVggDzZUqV4P2IucMeguUMaLBl5Uld7PeLHCo74/d3AcWCNCsGDxtW9Jm7+suyo6Y0jRY7OUi0PKZJre84m2q9Oo30AdgbMFRT91uZFYp9miJGWlQWwHhJDo3cU5iAYf5bnPPYgH8073kTFtmDPmrP9pvEUm3lsroUw=","gmt_create":"2016-09-23 19:08:09","app_id":"20151128001234567","seller_id":"20880114661234567","notify_id":"da3e56af64bcb163f167240dc0f781agge"}';

        $str = stripslashes($str);

        $data = json_decode($str, true);

        $signer = new Signer($data);
        $signer->setSort(true);
        $signer->setEncodePolicy(Signer::ENCODE_POLICY_QUERY);
        $data['sign']      = $signer->signWithRSA($testPrivateKey);
        $data['sign_type'] = 'RSA';

        $this->gateway->setAlipayPublicKey($testPublicKey);

        /**
         * @var AopCompletePurchaseResponse $response
         */
        $response = $this->gateway->completePurchase(['params' => $data])->send();

        $this->assertEquals(
            '{"total_amount":"0.01","buyer_id":"20882025611234567","trade_no":"201609232100100306021234567","refund_fee":"0.00","notify_time":"2016-09-23 19:12:33","subject":"test","sign_type":"RSA","notify_type":"trade_status_sync","out_trade_no":"2016092313071234567","gmt_close":"2016-09-23 19:08:10","trade_status":"TRADE_FINISHED","gmt_payment":"2016-09-23 19:08:10","sign":"Xa2NyOsxOBjW\/q\/RUFZhii2epa4B3ka+2aGsG8knqkiCD8llXrTDm11QtGkSRVw\/hbfcgFPiTkuaKnaaDu\/UfypsVSHToy28PiH5xkBSSd6zHNZCP\/jvjzOa6GPf4tIpfYNVvjaRMRcbn+TRlOFtHOnMMubjsg7K52P+LCugZIA=","gmt_create":"2016-09-23 19:08:09","app_id":"20151128001234567","seller_id":"20880114661234567","notify_id":"da3e56af64bcb163f167240dc0f781agge"}',
            json_encode($response->data())
        );

        $this->assertEquals('2016092313071234567', $response->data('out_trade_no'));
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isPaid());
        $this->assertEquals('201609232100100306021234567', $response->getData()['trade_no']);
    }


    public function testCompletePurchaseNotifyWithInlineKey()
    {
        $testPrivateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';
        $testPublicKey  = ALIPAY_ASSET_DIR . '/dist/common/rsa_public_key_inline.pem';

        $this->gateway = new AopAppGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey($this->appPrivateKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');

        $str = '{"total_amount":"0.01","buyer_id":"20882025611234567","trade_no":"201609232100100306021234567","refund_fee":"0.00","notify_time":"2016-09-23 19:12:33","subject":"test","sign_type":"RSA","notify_type":"trade_status_sync","out_trade_no":"2016092313071234567","gmt_close":"2016-09-23 19:08:10","trade_status":"TRADE_FINISHED","gmt_payment":"2016-09-23 19:08:10","sign":"vCAj0n6vUVggDzZUqV4P2IucMeguUMaLBl5Uld7PeLHCo74/d3AcWCNCsGDxtW9Jm7+suyo6Y0jRY7OUi0PKZJre84m2q9Oo30AdgbMFRT91uZFYp9miJGWlQWwHhJDo3cU5iAYf5bnPPYgH8073kTFtmDPmrP9pvEUm3lsroUw=","gmt_create":"2016-09-23 19:08:09","app_id":"20151128001234567","seller_id":"20880114661234567","notify_id":"da3e56af64bcb163f167240dc0f781agge"}';

        $str = stripslashes($str);

        $data = json_decode($str, true);

        $signer = new Signer($data);
        $signer->setSort(true);
        $signer->setEncodePolicy(Signer::ENCODE_POLICY_QUERY);
        $data['sign']      = $signer->signWithRSA($testPrivateKey);
        $data['sign_type'] = 'RSA';

        $this->gateway->setAlipayPublicKey($testPublicKey);

        /**
         * @var AopCompletePurchaseResponse $response
         */
        $response = $this->gateway->completePurchase(['params' => $data])->send();

        $this->assertEquals(
            '{"total_amount":"0.01","buyer_id":"20882025611234567","trade_no":"201609232100100306021234567","refund_fee":"0.00","notify_time":"2016-09-23 19:12:33","subject":"test","sign_type":"RSA","notify_type":"trade_status_sync","out_trade_no":"2016092313071234567","gmt_close":"2016-09-23 19:08:10","trade_status":"TRADE_FINISHED","gmt_payment":"2016-09-23 19:08:10","sign":"Xa2NyOsxOBjW\/q\/RUFZhii2epa4B3ka+2aGsG8knqkiCD8llXrTDm11QtGkSRVw\/hbfcgFPiTkuaKnaaDu\/UfypsVSHToy28PiH5xkBSSd6zHNZCP\/jvjzOa6GPf4tIpfYNVvjaRMRcbn+TRlOFtHOnMMubjsg7K52P+LCugZIA=","gmt_create":"2016-09-23 19:08:09","app_id":"20151128001234567","seller_id":"20880114661234567","notify_id":"da3e56af64bcb163f167240dc0f781agge"}',
            json_encode($response->data())
        );

        $this->assertEquals('2016092313071234567', $response->data('out_trade_no'));
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isPaid());
        $this->assertEquals('201609232100100306021234567', $response->getData()['trade_no']);
    }
}
