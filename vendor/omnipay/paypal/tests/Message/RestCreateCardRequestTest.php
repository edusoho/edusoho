<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Tests\TestCase;

class RestCreateCardRequestTest extends TestCase
{
    /** @var RestCreateCardRequest */
    protected $request;

    /** @var CreditCard */
    protected $card;

    public function setUp()
    {
        parent::setUp();

        $this->request = new RestCreateCardRequest($this->getHttpClient(), $this->getHttpRequest());

        $card = $this->getValidCard();
        $this->card = new CreditCard($card);

        $this->request->initialize(array('card' => $card));
    }

    public function testGetData()
    {
        $card = $this->card;
        $data = $this->request->getData();

        $this->assertSame($card->getNumber(), $data['number']);
        $this->assertSame($card->getBrand(), $data['type']);
        $this->assertSame($card->getExpiryMonth(), $data['expire_month']);
        $this->assertSame($card->getExpiryYear(), $data['expire_year']);
        $this->assertSame($card->getCvv(), $data['cvv2']);
        $this->assertSame($card->getFirstName(), $data['first_name']);
        $this->assertSame($card->getLastName(), $data['last_name']);
        $this->assertSame($card->getAddress1(), $data['billing_address']['line1']);
        $this->assertSame($card->getAddress2(), $data['billing_address']['line2']);
        $this->assertSame($card->getCity(), $data['billing_address']['city']);
        $this->assertSame($card->getState(), $data['billing_address']['state']);
        $this->assertSame($card->getPostcode(), $data['billing_address']['postal_code']);
        $this->assertSame($card->getCountry(), $data['billing_address']['country_code']);
    }
}
