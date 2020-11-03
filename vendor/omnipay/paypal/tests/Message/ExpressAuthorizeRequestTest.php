<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\PayPal\Message\ExpressAuthorizeRequest;
use Omnipay\PayPal\Support\InstantUpdateApi\BillingAgreement;
use Omnipay\PayPal\Support\InstantUpdateApi\ShippingOption;
use Omnipay\Tests\TestCase;

class ExpressAuthorizeRequestTest extends TestCase
{
    /**
     * @var ExpressAuthorizeRequest
     */
    private $request;

    public function setUp()
    {
        parent::setUp();

        $this->request = new ExpressAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '10.00',
                'returnUrl' => 'https://www.example.com/return',
                'cancelUrl' => 'https://www.example.com/cancel',
            )
        );
    }

    public function testGetDataWithoutCard()
    {
        $this->request->initialize(array(
            'amount' => '10.00',
            'currency' => 'AUD',
            'transactionId' => '111',
            'description' => 'Order Description',
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
            'subject' => 'demo@example.com',
            'headerImageUrl' => 'https://www.example.com/header.jpg',
            'noShipping' => 0,
            'localeCode' => 'EN',
            'allowNote' => 0,
            'addressOverride' => 0,
            'brandName' => 'Dunder Mifflin Paper Company, Inc.',
            'customerServiceNumber' => '1-801-FLOWERS',
        ));

        $data = $this->request->getData();

        $this->assertSame('10.00', $data['PAYMENTREQUEST_0_AMT']);
        $this->assertSame('AUD', $data['PAYMENTREQUEST_0_CURRENCYCODE']);
        $this->assertSame('111', $data['PAYMENTREQUEST_0_INVNUM']);
        $this->assertSame('Order Description', $data['PAYMENTREQUEST_0_DESC']);
        $this->assertSame('https://www.example.com/return', $data['RETURNURL']);
        $this->assertSame('https://www.example.com/cancel', $data['CANCELURL']);
        $this->assertSame('demo@example.com', $data['SUBJECT']);
        $this->assertSame('https://www.example.com/header.jpg', $data['HDRIMG']);
        $this->assertSame(0, $data['NOSHIPPING']);
        $this->assertSame(0, $data['ALLOWNOTE']);
        $this->assertSame('EN', $data['LOCALECODE']);
        $this->assertSame(0, $data['ADDROVERRIDE']);
        $this->assertSame('Dunder Mifflin Paper Company, Inc.', $data['BRANDNAME']);
        $this->assertSame('1-801-FLOWERS', $data['CUSTOMERSERVICENUMBER']);
    }

    public function testGetDataWithCard()
    {
        $this->request->initialize(array(
            'amount' => '10.00',
            'currency' => 'AUD',
            'transactionId' => '111',
            'description' => 'Order Description',
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
            'subject' => 'demo@example.com',
            'headerImageUrl' => 'https://www.example.com/header.jpg',
            'noShipping' => 2,
            'allowNote' => 1,
            'addressOverride' => 1,
            'brandName' => 'Dunder Mifflin Paper Company, Inc.',
            'maxAmount' => 123.45,
            'logoImageUrl' => 'https://www.example.com/logo.jpg',
            'borderColor' => 'CCCCCC',
            'localeCode' => 'EN',
            'customerServiceNumber' => '1-801-FLOWERS',
            'sellerPaypalAccountId' => 'billing@example.com',
        ));

        $card = new CreditCard(array(
            'name' => 'John Doe',
            'address1' => '123 NW Blvd',
            'address2' => 'Lynx Lane',
            'city' => 'Topeka',
            'state' => 'KS',
            'country' => 'USA',
            'postcode' => '66605',
            'phone' => '555-555-5555',
            'email' => 'test@email.com',
        ));
        $this->request->setCard($card);

        $expected = array(
            'METHOD' => 'SetExpressCheckout',
            'VERSION' => ExpressAuthorizeRequest::API_VERSION,
            'USER' => null,
            'PWD' => null,
            'SIGNATURE' => null,
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Authorization',
            'SOLUTIONTYPE' => null,
            'LANDINGPAGE' => null,
            'NOSHIPPING' => 2,
            'ALLOWNOTE' => 1,
            'ADDROVERRIDE' => 1,
            'PAYMENTREQUEST_0_AMT' => '10.00',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'AUD',
            'PAYMENTREQUEST_0_INVNUM' => '111',
            'PAYMENTREQUEST_0_DESC' => 'Order Description',
            'RETURNURL' => 'https://www.example.com/return',
            'CANCELURL' => 'https://www.example.com/cancel',
            'SUBJECT' => 'demo@example.com',
            'HDRIMG' => 'https://www.example.com/header.jpg',
            'PAYMENTREQUEST_0_SHIPTONAME' => 'John Doe',
            'PAYMENTREQUEST_0_SHIPTOSTREET' => '123 NW Blvd',
            'PAYMENTREQUEST_0_SHIPTOSTREET2' => 'Lynx Lane',
            'PAYMENTREQUEST_0_SHIPTOCITY' => 'Topeka',
            'PAYMENTREQUEST_0_SHIPTOSTATE' => 'KS',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'USA',
            'PAYMENTREQUEST_0_SHIPTOZIP' => '66605',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM' => '555-555-5555',
            'EMAIL' => 'test@email.com',
            'BRANDNAME' => 'Dunder Mifflin Paper Company, Inc.',
            'MAXAMT' => 123.45,
            'PAYMENTREQUEST_0_TAXAMT' => null,
            'PAYMENTREQUEST_0_SHIPPINGAMT' => null,
            'PAYMENTREQUEST_0_HANDLINGAMT' => null,
            'PAYMENTREQUEST_0_SHIPDISCAMT' => null,
            'PAYMENTREQUEST_0_INSURANCEAMT' => null,
            'LOGOIMG' => 'https://www.example.com/logo.jpg',
            'CARTBORDERCOLOR' => 'CCCCCC',
            'LOCALECODE' => 'EN',
            'CUSTOMERSERVICENUMBER' => '1-801-FLOWERS',
            'PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID' => 'billing@example.com',
        );

        $this->assertEquals($expected, $this->request->getData());
    }

    public function testGetDataWithItems()
    {
        $this->request->setItems(array(
            array('name' => 'Floppy Disk', 'description' => 'MS-DOS', 'quantity' => 2, 'price' => 10, 'code' => '123456'),
            array('name' => 'CD-ROM', 'description' => 'Windows 95', 'quantity' => 1, 'price' => 40),
        ));

        $data = $this->request->getData();
        $this->assertSame('Floppy Disk', $data['L_PAYMENTREQUEST_0_NAME0']);
        $this->assertSame('MS-DOS', $data['L_PAYMENTREQUEST_0_DESC0']);
        $this->assertSame(2, $data['L_PAYMENTREQUEST_0_QTY0']);
        $this->assertSame('10.00', $data['L_PAYMENTREQUEST_0_AMT0']);
        $this->assertSame('123456', $data['L_PAYMENTREQUEST_0_NUMBER0']);

        $this->assertSame('CD-ROM', $data['L_PAYMENTREQUEST_0_NAME1']);
        $this->assertSame('Windows 95', $data['L_PAYMENTREQUEST_0_DESC1']);
        $this->assertSame(1, $data['L_PAYMENTREQUEST_0_QTY1']);
        $this->assertSame('40.00', $data['L_PAYMENTREQUEST_0_AMT1']);

        $this->assertSame('60.00', $data['PAYMENTREQUEST_0_ITEMAMT']);
    }

    public function testGetDataWithExtraOrderDetails()
    {
        $this->request->initialize(array(
            'amount' => '10.00',
            'currency' => 'AUD',
            'transactionId' => '111',
            'description' => 'Order Description',
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
            'subject' => 'demo@example.com',
            'headerImageUrl' => 'https://www.example.com/header.jpg',
            'noShipping' => 0,
            'allowNote' => 0,
            'addressOverride' => 0,
            'brandName' => 'Dunder Mifflin Paper Company, Inc.',
            'taxAmount' => '2.00',
            'shippingAmount' => '5.00',
            'handlingAmount' => '1.00',
            'shippingDiscount' => '-1.00',
            'insuranceAmount' => '3.00',
        ));

        $data = $this->request->getData();
        $this->assertSame('2.00', $data['PAYMENTREQUEST_0_TAXAMT']);
        $this->assertSame('5.00', $data['PAYMENTREQUEST_0_SHIPPINGAMT']);
        $this->assertSame('1.00', $data['PAYMENTREQUEST_0_HANDLINGAMT']);
        $this->assertSame('-1.00', $data['PAYMENTREQUEST_0_SHIPDISCAMT']);
        $this->assertSame('3.00', $data['PAYMENTREQUEST_0_INSURANCEAMT']);
    }

    public function testHeaderImageUrl()
    {
        $this->assertSame($this->request, $this->request->setHeaderImageUrl('https://www.example.com/header.jpg'));
        $this->assertSame('https://www.example.com/header.jpg', $this->request->getHeaderImageUrl());

        $data = $this->request->getData();
        $this->assertEquals('https://www.example.com/header.jpg', $data['HDRIMG']);
    }

    public function testMaxAmount()
    {
        $this->request->setMaxAmount(321.54);

        $this->assertSame(321.54, $this->request->getMaxAmount());

        $data = $this->request->getData();

        $this->assertSame(321.54, $data['MAXAMT']);
    }

    public function testDataWithCallback()
    {
        $baseData = array(
            'amount' => '10.00',
            'currency' => 'AUD',
            'transactionId' => '111',
            'description' => 'Order Description',
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
            'subject' => 'demo@example.com',
            'headerImageUrl' => 'https://www.example.com/header.jpg',
            'allowNote' => 0,
            'addressOverride' => 0,
            'brandName' => 'Dunder Mifflin Paper Company, Incy.',
        );

        $shippingOptions = array(
            new ShippingOption('First Class', 1.20, true, '1-2 days'),
            new ShippingOption('Second Class', 0.70, false, '3-5 days'),
            new ShippingOption('International', 3.50),
        );

        // with a default callback timeout
        $this->request->initialize(array_merge($baseData, array(
            'callback' => 'https://www.example.com/calculate-shipping',
            'shippingOptions' => $shippingOptions,
        )));

        $data = $this->request->getData();
        $this->assertSame('https://www.example.com/calculate-shipping', $data['CALLBACK']);
        $this->assertSame(ExpressAuthorizeRequest::DEFAULT_CALLBACK_TIMEOUT, $data['CALLBACKTIMEOUT']);

        $this->assertSame('First Class', $data['L_SHIPPINGOPTIONNAME0']);
        $this->assertSame('1.20', $data['L_SHIPPINGOPTIONAMOUNT0']);
        $this->assertSame('1', $data['L_SHIPPINGOPTIONISDEFAULT0']);
        $this->assertSame('1-2 days', $data['L_SHIPPINGOPTIONLABEL0']);

        $this->assertSame('Second Class', $data['L_SHIPPINGOPTIONNAME1']);
        $this->assertSame('0.70', $data['L_SHIPPINGOPTIONAMOUNT1']);
        $this->assertSame('0', $data['L_SHIPPINGOPTIONISDEFAULT1']);
        $this->assertSame('3-5 days', $data['L_SHIPPINGOPTIONLABEL1']);

        $this->assertSame('International', $data['L_SHIPPINGOPTIONNAME2']);
        $this->assertSame('3.50', $data['L_SHIPPINGOPTIONAMOUNT2']);
        $this->assertSame('0', $data['L_SHIPPINGOPTIONISDEFAULT2']);

        // with a defined callback timeout
        $this->request->initialize(array_merge($baseData, array(
            'callback' => 'https://www.example.com/calculate-shipping',
            'callbackTimeout' => 10,
            'shippingOptions' => $shippingOptions,
        )));

        $data = $this->request->getData();
        $this->assertSame('https://www.example.com/calculate-shipping', $data['CALLBACK']);
        $this->assertSame(10, $data['CALLBACKTIMEOUT']);
    }

    public function testDataWithCallbackAndNoDefaultShippingOption()
    {
        $baseData = array(
            'amount' => '10.00',
            'currency' => 'AUD',
            'transactionId' => '111',
            'description' => 'Order Description',
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
            'subject' => 'demo@example.com',
            'headerImageUrl' => 'https://www.example.com/header.jpg',
            'allowNote' => 0,
            'addressOverride' => 0,
            'brandName' => 'Dunder Mifflin Paper Company, Incy.',
        );

        $shippingOptions = array(
            new ShippingOption('First Class', 1.20, false, '1-2 days'),
            new ShippingOption('Second Class', 0.70, false, '3-5 days'),
            new ShippingOption('International', 3.50),
        );

        // with a default callback timeout
        $this->request->initialize(array_merge($baseData, array(
            'callback' => 'https://www.example.com/calculate-shipping',
            'shippingOptions' => $shippingOptions,
        )));

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('One of the supplied shipping options must be set as default');

        $this->request->getData();
    }

    public function testNoAmount()
    {
        $baseData = array(// nothing here - should cause a certain exception
        );

        $this->request->initialize($baseData);

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The amount parameter is required');

        $this->request->getData();
    }

    public function testAmountButNoReturnUrl()
    {
        $baseData = array(
            'amount' => 10.00,
        );

        $this->request->initialize($baseData);

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The returnUrl parameter is required');

        $this->request->getData();
    }

    public function testBadCallbackConfiguration()
    {
        $baseData = array(
            'amount' => '10.00',
            'currency' => 'AUD',
            'transactionId' => '111',
            'description' => 'Order Description',
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
            'subject' => 'demo@example.com',
            'headerImageUrl' => 'https://www.example.com/header.jpg',
            'allowNote' => 0,
            'addressOverride' => 0,
            'brandName' => 'Dunder Mifflin Paper Company, Incy.',
        );

        $this->request->initialize(array_merge($baseData, array(
            'callback' => 'https://www.example.com/calculate-shipping',
        )));

        // from the docblock on this exception -
        // Thrown when a request is invalid or missing required fields.
        // callback has been set but no shipping options so expect one of these:
        $this->expectException(InvalidRequestException::class);

        $this->request->getData();
    }

    public function testGetDataWithSingleBillingAgreement()
    {
        $billingAgreement = new BillingAgreement(false, 'Some Stuff');
        $this->request->setBillingAgreement($billingAgreement);

        $data = $this->request->getData();

        $this->assertSame('MerchantInitiatedBillingSingleAgreement', $data['L_BILLINGTYPE0']);
        $this->assertSame('Some Stuff', $data['L_BILLINGAGREEMENTDESCRIPTION0']);
    }

    public function testGetDataWithRecurringBillingAgreement()
    {
        $billingAgreement = new BillingAgreement(true, 'Some Stuff');
        $this->request->setBillingAgreement($billingAgreement);

        $data = $this->request->getData();

        $this->assertSame('MerchantInitiatedBilling', $data['L_BILLINGTYPE0']);
        $this->assertSame('Some Stuff', $data['L_BILLINGAGREEMENTDESCRIPTION0']);
    }

    public function testGetDataWithBillingAgreementOptionalParameters()
    {
        $billingAgreement = new BillingAgreement(true, 'Some Stuff', 'InstantOnly', 'Some custom annotation');
        $this->request->setBillingAgreement($billingAgreement);

        $data = $this->request->getData();

        $this->assertSame('MerchantInitiatedBilling', $data['L_BILLINGTYPE0']);
        $this->assertSame('Some Stuff', $data['L_BILLINGAGREEMENTDESCRIPTION0']);
        $this->assertSame('InstantOnly', $data['L_PAYMENTTYPE0']);
        $this->assertSame('Some custom annotation', $data['L_BILLINGAGREEMENTCUSTOM0']);
    }

    /**
     *
     */
    public function testGetDataWithBillingAgreementWrongPaymentType()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("The 'paymentType' parameter can be only 'Any' or 'InstantOnly'");

        $billingAgreement = new BillingAgreement(false, 'Some Stuff', 'BadType', 'Some custom annotation');
    }
}
