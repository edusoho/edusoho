<?php

namespace Omnipay\PayPal;

use Omnipay\Tests\GatewayTestCase;

class ExpressGatewayTest extends GatewayTestCase
{
    /**
     * @var \Omnipay\PayPal\ExpressGateway
     */
    protected $gateway;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $voidOptions;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new ExpressGateway($this->getHttpClient(), $this->getHttpRequest());

        $this->options = array(
            'amount' => '10.00',
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
        );
        $this->voidOptions = array(
            'transactionReference' => 'ASDFASDFASDF',
        );
    }

    public function testAuthorizeSuccess()
    {
        $this->setMockHttpResponse('ExpressPurchaseSuccess.txt');

        $response = $this->gateway->authorize($this->options)->send();

        $this->assertInstanceOf('\Omnipay\PayPal\Message\ExpressAuthorizeResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=EC-42721413K79637829', $response->getRedirectUrl());
    }

    public function testAuthorizeFailure()
    {
        $this->setMockHttpResponse('ExpressPurchaseFailure.txt');

        $response = $this->gateway->authorize($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('This transaction cannot be processed. The amount to be charged is zero.', $response->getMessage());
    }

    public function testPurchaseSuccess()
    {
        $this->setMockHttpResponse('ExpressPurchaseSuccess.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertInstanceOf('\Omnipay\PayPal\Message\ExpressAuthorizeResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=EC-42721413K79637829', $response->getRedirectUrl());
    }

    public function testPurchaseFailure()
    {
        $this->setMockHttpResponse('ExpressPurchaseFailure.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('This transaction cannot be processed. The amount to be charged is zero.', $response->getMessage());
    }

    public function testOrderSuccess()
    {
        $this->setMockHttpResponse('ExpressOrderSuccess.txt');

        $response = $this->gateway->order($this->options)->send();

        $this->assertInstanceOf('\Omnipay\PayPal\Message\ExpressAuthorizeResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=EC-42721413K79637829', $response->getRedirectUrl());
    }

    public function testOrderFailure()
    {
        $this->setMockHttpResponse('ExpressOrderFailure.txt');

        $response = $this->gateway->order($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('This transaction cannot be processed. The amount to be charged is zero.', $response->getMessage());
    }

    public function testVoidSuccess()
    {
        $this->setMockHttpResponse('ExpressVoidSuccess.txt');

        $response = $this->gateway->void($this->voidOptions)->send();

        $this->assertInstanceOf('\Omnipay\PayPal\Message\Response', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('ASDFASDFASDF', $response->getTransactionReference());
    }

    public function testVoidFailure()
    {
        $this->setMockHttpResponse('ExpressVoidFailure.txt');

        $response = $this->gateway->void($this->voidOptions)->send();

        $this->assertInstanceOf('\Omnipay\PayPal\Message\Response', $response);
        $this->assertFalse($response->isSuccessful());
    }

    public function testFetchCheckout()
    {
        $options = array('token' => 'abc123');
        $request = $this->gateway->fetchCheckout($options);

        $this->assertInstanceOf('\Omnipay\PayPal\Message\ExpressFetchCheckoutRequest', $request);
        $this->assertSame('abc123', $request->getToken());
    }

    public function testCompletePurchaseFailureRedirect()
    {
        $this->setMockHttpResponse('ExpressCompletePurchaseFailureRedirect.txt');

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('ASDFASDFASDF', $response->getTransactionReference());
        $this->assertSame('This transaction couldn\'t be completed. Please redirect your customer to PayPal.', $response->getMessage());
    }

    public function testCompletePurchaseHttpOptions()
    {
        $this->setMockHttpResponse('ExpressPurchaseSuccess.txt');

        $this->getHttpRequest()->query->replace(array(
            'token' => 'GET_TOKEN',
            'PayerID' => 'GET_PAYERID',
        ));

        $response = $this->gateway->completePurchase(array(
            'amount' => '10.00',
            'currency' => 'EUR',
        ))->send();

        $httpRequests = $this->getMockedRequests();
        $httpRequest = $httpRequests[0];
        parse_str((string)$httpRequest->getBody(), $postData);
        $this->assertSame('GET_TOKEN', $postData['TOKEN']);
        $this->assertSame('GET_PAYERID', $postData['PAYERID']);
    }

    public function testCompletePurchaseCustomOptions()
    {
        $this->setMockHttpResponse('ExpressPurchaseSuccess.txt');

        // Those values should not be used if custom token or payerid are passed
        $this->getHttpRequest()->query->replace(array(
            'token' => 'GET_TOKEN',
            'PayerID' => 'GET_PAYERID',
        ));

        $response = $this->gateway->completePurchase(array(
            'amount' => '10.00',
            'currency' => 'EUR',
            'token' => 'CUSTOM_TOKEN',
            'payerid' => 'CUSTOM_PAYERID',
        ))->send();

        $httpRequests = $this->getMockedRequests();
        $httpRequest = $httpRequests[0];
        parse_str((string)$httpRequest->getBody(), $postData);
        $this->assertSame('CUSTOM_TOKEN', $postData['TOKEN']);
        $this->assertSame('CUSTOM_PAYERID', $postData['PAYERID']);
    }

    public function testTransactionSearch()
    {
        $transactionSearch = $this->gateway->transactionSearch(array(
            'startDate' => '2015-01-01',
            'endDate' => '2015-12-31',
        ));

        $this->assertInstanceOf('\Omnipay\PayPal\Message\ExpressTransactionSearchRequest', $transactionSearch);
        $this->assertInstanceOf('\DateTime', $transactionSearch->getStartDate());
        $this->assertInstanceOf('\DateTime', $transactionSearch->getEndDate());
    }
}
