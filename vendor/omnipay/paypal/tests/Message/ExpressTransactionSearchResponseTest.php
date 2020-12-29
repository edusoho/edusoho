<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Tests\TestCase;

class ExpressTransactionSearchResponseTest extends TestCase
{
    public function testConstruct()
    {
        // response should decode URL format data
        $response = new ExpressTransactionSearchResponse($this->getMockRequest(), 'ACK=Success&BUILD=18308778');

        $this->assertEquals(
            array('ACK' => 'Success', 'BUILD' => '18308778', 'payments' => array()),
            $response->getData()
        );
    }

    public function testExpressTransactionSearch()
    {
        $httpResponse = $this->getMockHttpResponse('ExpressTransactionSearchResponse.txt');

        $response = new ExpressTransactionSearchResponse($this->getMockRequest(), $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getMessage());
        $this->assertArrayHasKey('payments', $response->getData());

        foreach ($response->getPayments() as $payment) {
            $this->assertArrayHasKey('TIMESTAMP', $payment);
            $this->assertArrayHasKey('TIMEZONE', $payment);
            $this->assertArrayHasKey('TYPE', $payment);
            $this->assertArrayHasKey('EMAIL', $payment);
            $this->assertArrayHasKey('NAME', $payment);
            $this->assertArrayHasKey('TRANSACTIONID', $payment);
            $this->assertArrayHasKey('STATUS', $payment);
            $this->assertArrayHasKey('AMT', $payment);
            $this->assertArrayHasKey('CURRENCYCODE', $payment);
            $this->assertArrayHasKey('FEEAMT', $payment);
            $this->assertArrayHasKey('NETAMT', $payment);
        }
    }
}