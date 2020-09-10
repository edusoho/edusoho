<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Tests\TestCase;
use Omnipay\PayPal\RestGateway;

class RestCreatePlanRequestTest extends TestCase
{
    /** @var \Omnipay\PayPal\Message\RestCreatePlanRequest */
    private $request;

    public function setUp()
    {
        $client = $this->getHttpClient();
        $request = $this->getHttpRequest();
        $this->request = new RestCreatePlanRequest($client, $request);

        $this->request->initialize(array(
            'name'                  => 'Super Duper Billing Plan',
            'description'           => 'Test Billing Plan',
            'type'                  => RestGateway::BILLING_PLAN_TYPE_FIXED,
            'paymentDefinitions'    => array(
                array(
                    'name'                  => 'Monthly Payments',
                    'type'                  => RestGateway::PAYMENT_REGULAR,
                    'frequency'             => RestGateway::BILLING_PLAN_FREQUENCY_MONTH,
                    'frequency_interval'    => 1,
	                'cycles'                => 12,
	                'amount'                => array(
                        'value'     => 10.00,
                        'currency'  => 'USD',
                    )
                )
            ),
            'merchantPreferences'    => array(
                'name'  => 'asdf',
            ),
        ));
    }

    public function testGetData()
    {
        $data = $this->request->getData();
        $this->assertEquals('Super Duper Billing Plan', $data['name']);
        $this->assertEquals(RestGateway::BILLING_PLAN_TYPE_FIXED, $data['type']);
        $this->assertEquals('Monthly Payments', $data['payment_definitions'][0]['name']);
    }
}
