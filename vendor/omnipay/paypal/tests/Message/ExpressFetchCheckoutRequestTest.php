<?php

namespace Omnipay\PayPal\Message;

use Omnipay\PayPal\Message\ExpressFetchCheckoutRequest;
use Omnipay\Tests\TestCase;

class ExpressFetchCheckoutRequestTest extends TestCase
{
    /**
     * @var \Omnipay\PayPal\Message\ExpressFetchCheckoutRequest
     */
    private $request;

    public function setUp()
    {
        $client = $this->getHttpClient();

        $request = $this->getHttpRequest();
        $request->query->set('token', 'TOKEN1234');

        $this->request = new ExpressFetchCheckoutRequest($client, $request);
    }

    public function testGetData()
    {
        $this->request->setUsername('testuser');
        $this->request->setPassword('testpass');
        $this->request->setSignature('SIG');

        $expected = array();
        $expected['METHOD'] = 'GetExpressCheckoutDetails';
        $expected['USER'] = 'testuser';
        $expected['PWD'] = 'testpass';
        $expected['SIGNATURE'] = 'SIG';
        $expected['SUBJECT'] = null;
        $expected['VERSION'] = ExpressCompletePurchaseRequest::API_VERSION;
        $expected['TOKEN'] = 'TOKEN1234';

        $this->assertEquals($expected, $this->request->getData());
    }

    public function testGetDataTokenOverride()
    {
        $this->request->setToken('TOKEN2000');

        $data = $this->request->getData();

        $this->assertSame('TOKEN2000', $data['TOKEN']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('ExpressFetchCheckoutSuccess.txt');

        $response = $this->request->send();
        $this->assertFalse($response->isPending());
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('ExpressFetchCheckoutFailure.txt');

        $response = $this->request->send();
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('The amount exceeds the maximum amount for a single transaction.', $response->getMessage());
    }
}
