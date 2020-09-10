<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Tests\TestCase;
use Omnipay\PayPal\RestGateway;

class RestCompleteSubscriptionRequestTest extends TestCase
{
    /** @var \Omnipay\PayPal\Message\RestCompleteSubscriptionRequest */
    private $request;

    public function setUp()
    {
        $client = $this->getHttpClient();
        $request = $this->getHttpRequest();
        $this->request = new RestCompleteSubscriptionRequest($client, $request);

        $this->request->initialize(array(
            'transactionReference'  => 'ABC-123',
        ));
    }

    public function testGetData()
    {
        $data = $this->request->getData();
        $this->assertEquals(array(), $data);
    }
}
