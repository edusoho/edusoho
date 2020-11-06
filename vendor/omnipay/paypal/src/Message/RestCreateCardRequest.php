<?php
/**
 * PayPal REST Create Card Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Create Card Request
 *
 * PayPal offers merchants a /vault API to store sensitive details
 * like credit card related details.
 *
 * You can currently use the /vault API to store credit card details
 * with PayPal instead of storing them on your own server. After storing
 * a credit card, you can then pass the credit card id instead of the
 * related credit card details to complete a payment.
 *
 * Direct credit card payment and related features are restricted in
 * some countries.
 * As of January 2015 these transactions are only supported in the UK
 * and in the USA.
 *
 * Example:
 *
 * <code>
 *   // Create a gateway for the PayPal RestGateway
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('PayPal_Rest');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'clientId' => 'MyPayPalClientId',
 *       'secret'   => 'MyPayPalSecret',
 *       'testMode' => true, // Or false when you are ready for live transactions
 *   ));
 *
 *   // Create a credit card object
 *   // DO NOT USE THESE CARD VALUES -- substitute your own
 *   // see the documentation in the class header.
 *   $card = new CreditCard(array(
 *               'firstName' => 'Example',
 *               'lastName' => 'User',
 *               'number' => '4111111111111111',
 *               'expiryMonth'           => '01',
 *               'expiryYear'            => '2020',
 *               'cvv'                   => '123',
 *               'billingAddress1'       => '1 Scrubby Creek Road',
 *               'billingCountry'        => 'AU',
 *               'billingCity'           => 'Scrubby Creek',
 *               'billingPostcode'       => '4999',
 *               'billingState'          => 'QLD',
 *   ));
 *
 *   // Do a create card transaction on the gateway
 *   $transaction = $gateway->createCard(array(
 *       'card'          => $card,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Create card transaction was successful!\n";
 *       // Find the card ID
 *       $card_id = $response->getTransactionReference();
 *   }
 * </code>
 *
 * @link https://developer.paypal.com/docs/api/#vault
 * @link https://developer.paypal.com/docs/api/#store-a-credit-card
 * @link http://bit.ly/1wUQ33R
 */
class RestCreateCardRequest extends AbstractRestRequest
{
    public function getData()
    {
        $this->validate('card');
        $this->getCard()->validate();

        $data = array(
            'number' => $this->getCard()->getNumber(),
            'type' => $this->getCard()->getBrand(),
            'expire_month' => $this->getCard()->getExpiryMonth(),
            'expire_year' => $this->getCard()->getExpiryYear(),
            'cvv2' => $this->getCard()->getCvv(),
            'first_name' => $this->getCard()->getFirstName(),
            'last_name' => $this->getCard()->getLastName(),
            'billing_address' => array(
                'line1' => $this->getCard()->getAddress1(),
                //'line2' => $this->getCard()->getAddress2(),
                'city' => $this->getCard()->getCity(),
                'state' => $this->getCard()->getState(),
                'postal_code' => $this->getCard()->getPostcode(),
                'country_code' => strtoupper($this->getCard()->getCountry()),
            )
        );

        // There's currently a quirk with the REST API that requires line2 to be
        // non-empty if it's present. Jul 14, 2014
        $line2 = $this->getCard()->getAddress2();
        if (!empty($line2)) {
            $data['billing_address']['line2'] = $line2;
        }

        return $data;
    }

    protected function getEndpoint()
    {
        return parent::getEndpoint() . '/vault/credit-cards';
    }
}
