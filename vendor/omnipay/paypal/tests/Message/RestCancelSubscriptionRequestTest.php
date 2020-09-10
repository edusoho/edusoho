<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Tests\TestCase;
use Omnipay\PayPal\RestGateway;

class RestCancelSubscriptionRequestTest extends TestCase
{
    /** @var \Omnipay\PayPal\Message\RestCancelSubscriptionRequest */
    private $request;

    public function setUp()
    {
        $client = $this->getHttpClient();
        $request = $this->getHttpRequest();
        $this->request = new RestCancelSubscriptionRequest($client, $request);

        $this->request->initialize(array(
            'transactionReference'  => 'ABC-123',
            'description'           => 'Cancel this subscription',
        ));
    }

    public function testGetData()
    {
        $data = $this->request->getData();
        $this->assertEquals('Cancel this subscription', $data['note']);
    }
}
