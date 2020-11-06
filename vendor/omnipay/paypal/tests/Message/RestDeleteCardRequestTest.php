<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Tests\TestCase;

class RestDeleteCardRequestTest extends TestCase
{
    /** @var RestDeleteCardRequest */
    private $request;

    /** @var CreditCard */
    private $card;

    public function setUp()
    {
        parent::setUp();

        $this->request = new RestDeleteCardRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(array('cardReference' => 'CARD-TEST123'));
    }

    public function testGetData()
    {
        $data = $this->request->getData();
        $this->assertTrue(is_array($data));
        $this->assertEmpty($data);
    }

    public function testEndpoint()
    {
        $this->assertStringEndsWith('/vault/credit-cards/CARD-TEST123', $this->request->getEndpoint());
    }
}
