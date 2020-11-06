<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class ExpressTransactionSearchRequestTest extends TestCase
{
    /**
     * @var ExpressTransactionSearchRequest
     */
    private $request;

    public function setUp()
    {
        parent::setUp();

        $this->request = new ExpressTransactionSearchRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testGetData()
    {
        $startDate = '2015-01-01';
        $endDate = '2016-01-01';

        $this->request->initialize(array(
            'amount' => '10.00',
            'currency' => 'USD',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'salutation' => 'Mr.',
            'firstName' => 'Jhon',
            'middleName' => 'Carter',
            'lastName' => 'Macgiver',
            'suffix' => 'Jh',
            'email' => 'test@email.com',
            'receiver' => 'Patt Doret',
            'receiptId' => '1111',
            'transactionId' => 'XKCD',
            'invoiceNumber' => '123456789',
            'card' => array('number' => '376449047333005'),
            'auctionItemNumber' => '321564',
            'transactionClass' => 'Received',
            'status' => 'Success',
            'profileId' => '00000000000'
        ));

        $data = $this->request->getData();

        $startDate = new \DateTime($startDate);
        $endDate = new \DateTime($endDate);

        $this->assertSame('10.00', $data['AMT']);
        $this->assertSame('USD', $data['CURRENCYCODE']);
        $this->assertSame($startDate->format(\DateTime::ISO8601), $data['STARTDATE']);
        $this->assertSame($endDate->format(\DateTime::ISO8601), $data['ENDDATE']);
        $this->assertSame('Mr.', $data['SALUTATION']);
        $this->assertSame('Jhon', $data['FIRSTNAME']);
        $this->assertSame('Carter', $data['MIDDLENAME']);
        $this->assertSame('Macgiver', $data['LASTNAME']);
        $this->assertSame('Jh', $data['SUFFIX']);
        $this->assertSame('test@email.com', $data['EMAIL']);
        $this->assertSame('XKCD', $data['TRANSACTIONID']);
        $this->assertSame('123456789', $data['INVNUM']);
        $this->assertSame('376449047333005', $data['ACCT']);
        $this->assertSame('321564', $data['AUCTIONITEMNUMBER']);
        $this->assertSame('Received', $data['TRANSACTIONCLASS']);
        $this->assertSame('Success', $data['STATUS']);
        $this->assertSame('00000000000', $data['PROFILEID']);
    }

    public function testWithoutStartDate()
    {
        $this->request->initialize(array());

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The startDate parameter is required');

        $this->request->getData();
    }

    public function testAmountWithoutCurrency()
    {
        $this->request->setStartDate('2015-01-01');
        $this->request->setAmount(150.00);
        
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The currency parameter is required');

        $this->request->getData();
    }
}
