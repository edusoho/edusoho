<?php


namespace Omnipay\PayPal\Message;


use Omnipay\Tests\TestCase;

class RestCompletePurchaseRequestTest extends TestCase
{
    /**
     * @var RestCompletePurchaseRequest
     */
    private $request;


    public function setUp()
    {
        parent::setUp();

        $client = $this->getHttpClient();

        $request = $this->getHttpRequest();
        $this->request = new RestCompletePurchaseRequest($client, $request);
        $this->request->initialize(array());
    }

    public function testGetData()
    {
        $this->request->setTransactionReference('abc123');
        $this->request->setPayerId('Payer12345');

        $data = $this->request->getData();

        $this->assertSame('Payer12345', $data['payer_id']);
    }
}