<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Tests\TestCase;
use Omnipay\PayPal\RestGateway;

class RestSearchTransactionRequestTest extends TestCase
{
    /** @var \Omnipay\PayPal\Message\RestSearchTransactionRequest */
    private $request;

    public function setUp()
    {
        $client = $this->getHttpClient();
        $request = $this->getHttpRequest();
        $this->request = new RestSearchTransactionRequest($client, $request);

        $this->request->initialize(array(
            'agreementId'       => 'ABC-123',
            'startDate'         => '2015-09-01',
            'endDate'           => '2015-09-30',
        ));
    }

    public function testGetData()
    {
        $data = $this->request->getData();
        $this->assertEquals('2015-09-01', $data['start_date']);
        $this->assertEquals('2015-09-30', $data['end_date']);
    }

    public function testEndpoint()
    {
        $this->assertStringEndsWith('/payments/billing-agreements/ABC-123/transactions', $this->request->getEndpoint());
    }
}
