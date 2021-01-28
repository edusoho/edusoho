<?php

namespace Omnipay\Alipay\Tests;

use Omnipay\Alipay\AopF2FGateway;
use Omnipay\Alipay\Common\Signer;
use Omnipay\Alipay\Responses\AopCompletePurchaseResponse;
use Omnipay\Alipay\Responses\AopCompleteRefundResponse;
use Omnipay\Alipay\Responses\DataServiceBillDownloadUrlQueryResponse;
use Omnipay\Alipay\Responses\AopTradePayResponse;
use Omnipay\Alipay\Responses\AopTradePreCreateResponse;
use Omnipay\Alipay\Responses\AopTradeQueryResponse;
use Omnipay\Alipay\Responses\AopTradeRefundQueryResponse;
use Omnipay\Alipay\Responses\AopTradeRefundResponse;

class AopF2FGatewayTest extends AbstractGatewayTestCase
{

    /**
     * @var AopF2FGateway $gateway
     */
    protected $gateway;

    protected $options;


    public function setUp()
    {
        parent::setUp();
        $this->gateway = new AopF2FGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey($this->appPrivateKey);
        $this->gateway->setEncryptKey($this->appEncryptKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');
        $this->gateway->sandbox();
    }


    public function testCapture()
    {
        $this->setMockHttpResponse('AopF2F_Capture_Failure.txt');

        /**
         * @var AopTradePayResponse $response
         */
        $response = $this->gateway->capture(
            [
                'biz_content' => [
                    'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
                    'scene'        => 'bar_code',
                    'auth_code'    => '288412621343841260',
                    'subject'      => 'test',
                    'total_amount' => '0.01',
                ]
            ]
        )->setPollingAttempts(1)->send();

        $this->assertArrayHasKey('alipay_trade_pay_response', $response->getData());
        $this->assertFalse($response->isSuccessful());
    }


    public function testPurchase()
    {
        $this->setMockHttpResponse('AopF2F_Purchase_Failure.txt');

        /**
         * @var AopTradePreCreateResponse $response
         */
        $response = $this->gateway->purchase(
            [
                'biz_content' => [
                    'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
                    'subject'      => 'test',
                    'total_amount' => '0.01',
                ]
            ]
        )->send();

        $this->assertArrayHasKey('alipay_trade_precreate_response', $response->getData());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }


    public function testQuery()
    {
        $this->setMockHttpResponse('AopF2F_Query_Failure.txt');

        /**
         * @var AopTradeQueryResponse $response
         */
        $response = $this->gateway->query(
            [
                'biz_content' => [
                    'out_trade_no' => '201609220542532413'
                ]
            ]
        )->send();

        $this->assertArrayHasKey('alipay_trade_query_response', $response->getData());
        $this->assertFalse($response->isSuccessful());
    }


    public function testRefund()
    {
        $this->setMockHttpResponse('AopF2F_Refund_Failure.txt');

        /**
         * @var AopTradeRefundResponse $response
         */
        $response = $this->gateway->refund(
            [
                'biz_content' => [
                    'refund_amount' => '10.01',
                    'out_trade_no'  => '201609220542532413'
                ]
            ]
        )->send();

        $this->assertArrayHasKey('alipay_trade_refund_response', $response->getData());
        $this->assertFalse($response->isSuccessful());
    }


    public function testQueryRefund()
    {
        $this->setMockHttpResponse('AopF2F_QueryRefund_Failure.txt');

        /**
         * @var AopTradeRefundQueryResponse $response
         */
        $response = $this->gateway->refundQuery(
            [
                'biz_content' => [
                    'refund_amount'  => '10.01',
                    'out_trade_no'   => '201609220542532412',
                    'out_request_no' => '201609220542532412'
                ]
            ]
        )->send();

        $this->assertArrayHasKey('alipay_trade_fastpay_refund_query_response', $response->getData());
        $this->assertFalse($response->isSuccessful());
    }


    public function testSettle()
    {
        $this->setMockHttpResponse('AopF2F_Settle_Failure.txt');

        /**
         * @var AopTradeRefundQueryResponse $response
         */
        $response = $this->gateway->settle(
            [
                'biz_content' => [
                    'out_request_no'     => '201609220542532412',
                    'trade_no'           => '2014030411001007850000672009',
                    'royalty_parameters' => [
                        [
                            'trans_out' => '111111',
                            'trans_in'  => '222222',
                            'amount'    => '0.01',
                        ],
                        [
                            'trans_out' => '111111',
                            'trans_in'  => '333333',
                            'amount'    => '0.02',
                        ]
                    ]
                ]
            ]
        )->send();

        $this->assertArrayHasKey('alipay_trade_order_settle_response', $response->getData());
        $this->assertFalse($response->isSuccessful());
    }


    public function testQueryBillDownloadUrl()
    {
        $this->setMockHttpResponse('AopF2F_QueryBillDownloadUrl_Failure.txt');

        /**
         * @var DataServiceBillDownloadUrlQueryResponse $response
         */
        $response = $this->gateway->queryBillDownloadUrl(
            [
                'biz_content' => [
                    'bill_type' => 'trade',
                    'bill_date' => '1999-04-05',
                ]
            ]
        )->send();
        $this->assertArrayHasKey('alipay_data_dataservice_bill_downloadurl_query_response', $response->getData());
        $this->assertFalse($response->isSuccessful());
    }


    public function testCompletePurchase()
    {
        $testPrivateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';
        $testPublicKey  = ALIPAY_ASSET_DIR . '/dist/common/rsa_public_key.pem';

        $this->gateway = new AopF2FGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey($this->appPrivateKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');

        $str = 'gmt_payment=2015-06-11 22:33:59&notify_id=42af7baacd1d3746cf7b56752b91edcj34&seller_email=testyufabu07@alipay.com&notify_type=trade_status_sync&sign=kPbQIjX+xQc8F0/A6/AocELIjhhZnGbcBN6G4MM/HmfWL4ZiHM6fWl5NQhzXJusaklZ1LFuMo+lHQUELAYeugH8LYFvxnNajOvZhuxNFbN2LhF0l/KL8ANtj8oyPM4NN7Qft2kWJTDJUpQOzCzNnV9hDxh5AaT9FPqRS6ZKxnzM=&trade_no=2015061121001004400068549373&out_trade_no=21repl2ac2eOutTradeNo322&gmt_create=2015-06-11 22:33:46&seller_id=2088211521646673&notify_time=2015-06-11 22:34:03&subject=FACE_TO_FACE_PAYMENT_PRECREATE中文&trade_status=TRADE_SUCCESS&sign_type=RSA';

        parse_str($str, $data);

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
            '{"gmt_payment":"2015-06-11 22:33:59","notify_id":"42af7baacd1d3746cf7b56752b91edcj34","seller_email":"testyufabu07@alipay.com","notify_type":"trade_status_sync","sign":"T4JCUXoO5sK\/7UjupKEfsSQnjDnw\/1aSJnC6s53SYJyqdjFl+1Lt8dWdNuuXl5yX39leQsYzmk2CDwZx6F\/YIQWCo1LHZME3DYMqH\/F5wT5uiSUk2KYsYbLluW9pi7YHtBXRWKB6jtnn73DWWbC2sN3tDky9KySPizL5jQ1Cd0I=","trade_no":"2015061121001004400068549373","out_trade_no":"21repl2ac2eOutTradeNo322","gmt_create":"2015-06-11 22:33:46","seller_id":"2088211521646673","notify_time":"2015-06-11 22:34:03","subject":"FACE_TO_FACE_PAYMENT_PRECREATE\u4e2d\u6587","trade_status":"TRADE_SUCCESS","sign_type":"RSA"}',
            json_encode($response->data())
        );

        $this->assertEquals('21repl2ac2eOutTradeNo322', $response->data('out_trade_no'));
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isPaid());
        $this->assertEquals('2015061121001004400068549373', $response->getData()['trade_no']);
    }

    public function testCompleteRefund()
    {
        $testPrivateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';
        $testPublicKey  = ALIPAY_ASSET_DIR . '/dist/common/rsa_public_key.pem';

        $this->gateway = new AopF2FGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey($this->appPrivateKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');

        $str = 'gmt_payment=2015-06-11 22:33:59&notify_id=42af7baacd1d3746cf7b56752b91edcj34&seller_email=testyufabu07@alipay.com&notify_type=trade_status_sync&sign=kPbQIjX+xQc8F0/A6/AocELIjhhZnGbcBN6G4MM/HmfWL4ZiHM6fWl5NQhzXJusaklZ1LFuMo+lHQUELAYeugH8LYFvxnNajOvZhuxNFbN2LhF0l/KL8ANtj8oyPM4NN7Qft2kWJTDJUpQOzCzNnV9hDxh5AaT9FPqRS6ZKxnzM=&trade_no=2015061121001004400068549373&out_trade_no=21repl2ac2eOutTradeNo322&gmt_create=2015-06-11 22:33:46&seller_id=2088211521646673&notify_time=2015-06-11 22:34:03&subject=FACE_TO_FACE_PAYMENT_PRECREATE中文&trade_status=TRADE_SUCCESS&sign_type=RSA';

        parse_str($str, $data);

        $signer = new Signer($data);
        $signer->setSort(true);
        $signer->setEncodePolicy(Signer::ENCODE_POLICY_QUERY);
        $data['sign']      = $signer->signWithRSA($testPrivateKey);
        $data['sign_type'] = 'RSA';

        $this->gateway->setAlipayPublicKey($testPublicKey);

        /**
         * @var AopCompleteRefundResponse $response
         */
        $response = $this->gateway->completeRefund(['params' => $data])->send();

        $this->assertEquals(
            '{"gmt_payment":"2015-06-11 22:33:59","notify_id":"42af7baacd1d3746cf7b56752b91edcj34","seller_email":"testyufabu07@alipay.com","notify_type":"trade_status_sync","sign":"T4JCUXoO5sK\/7UjupKEfsSQnjDnw\/1aSJnC6s53SYJyqdjFl+1Lt8dWdNuuXl5yX39leQsYzmk2CDwZx6F\/YIQWCo1LHZME3DYMqH\/F5wT5uiSUk2KYsYbLluW9pi7YHtBXRWKB6jtnn73DWWbC2sN3tDky9KySPizL5jQ1Cd0I=","trade_no":"2015061121001004400068549373","out_trade_no":"21repl2ac2eOutTradeNo322","gmt_create":"2015-06-11 22:33:46","seller_id":"2088211521646673","notify_time":"2015-06-11 22:34:03","subject":"FACE_TO_FACE_PAYMENT_PRECREATE\u4e2d\u6587","trade_status":"TRADE_SUCCESS","sign_type":"RSA"}',
            json_encode($response->data())
        );

        $this->assertEquals('21repl2ac2eOutTradeNo322', $response->data('out_trade_no'));
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isRefunded());
        $this->assertEquals('2015061121001004400068549373', $response->getData()['trade_no']);
    }
}
