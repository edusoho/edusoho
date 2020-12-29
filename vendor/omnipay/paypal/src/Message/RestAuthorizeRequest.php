<?php
/**
 * PayPal REST Authorize Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Authorize Request
 *
 * To collect payment at a later time, first authorize a payment using the /payment resource.
 * You can then capture the payment to complete the sale and collect payment.
 *
 * This looks exactly like a RestPurchaseRequest object except that the intent is
 * set to "authorize" (to authorize a payment to be captured later) rather than
 * "sale" (which is used to capture a payment immediately).
 *
 * ### Example
 *
 * #### Initialize Gateway
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
 * </code>
 *
 * #### Direct Credit Card Authorize
 *
 * This is for the use case where a customer has presented their
 * credit card details and you intend to use the PayPal REST gateway
 * for processing a transaction using that credit card data.
 *
 * This does not require the customer to have a PayPal account.
 *
 * </code>
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
 *   // Do an authorisation transaction on the gateway
 *   $transaction = $gateway->authorize(array(
 *       'amount'        => '10.00',
 *       'currency'      => 'AUD',
 *       'description'   => 'This is a test authorize transaction.',
 *       'card'          => $card,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Authorize transaction was successful!\n";
 *       // Find the authorization ID
 *       $auth_id = $response->getTransactionReference();
 *   }
 * </code>
 *
 * Direct credit card payment and related features are restricted in
 * some countries.
 * As of January 2015 these transactions are only supported in the UK
 * and in the USA.
 *
 * #### PayPal Account Authorization
 *
 * This is for the use case where the customer intends to pay using their
 * PayPal account.  Note that no credit card details are provided, instead
 * both a return URL and a cancel URL are required.
 *
 * The optimal solution here is to provide a unique return URL and cancel
 * URL per transaction. That way your code will know what transaction is
 * being returned or cancelled by PayPal.
 *
 * So step 1 is to store some transaction data somewhere on your system so
 * that you have an ID when your transaction returns.  How you do this of
 * course depends on what framework, database layer, etc, you are using but
 * for this step let's assume that you have a class set up that can save
 * a transaction and return the object, and that you can retrieve the ID
 * of that saved object using some call like getId() on the object.  Most
 * ORMs such as Doctrine ORM, Propel or Eloquent will have some methods
 * that will allow you to do this or something similar.
 *
 * <code>
 *   $transaction = MyClass::saveTransaction($some_data);
 *   $txn_id = $transaction->getId();
 * </code>
 *
 * Step 2 is to send the purchase request.
 *
 * </code>
 *   // Do a purchase transaction on the gateway
 *   try {
 *       $transaction = $gateway->authorize(array(
 *           'amount'        => '10.00',
 *           'currency'      => 'AUD',
 *           'description'   => 'This is a test authorize transaction.',
 *           'returnUrl'     => 'http://mysite.com/paypal/return/?txn_id=' . $txn_id,
 *           'cancelUrl'     => 'http://mysite.com/paypal/return/?txn_id=' . $txn_id,
 *       ));
 *       $response = $transaction->send();
 *       $data = $response->getData();
 *       echo "Gateway purchase response data == " . print_r($data, true) . "\n";
 *
 *       if ($response->isSuccessful()) {
 *           echo "Step 2 was successful!\n";
 *       }
 *
 *   } catch (\Exception $e) {
 *       echo "Exception caught while attempting authorize.\n";
 *       echo "Exception type == " . get_class($e) . "\n";
 *       echo "Message == " . $e->getMessage() . "\n";
 *   }
 * </code>
 *
 * Step 3 is where your code needs to redirect the customer to the PayPal
 * gateway so that the customer can sign in to their PayPal account and
 * agree to authorize the payment.  The response will implement an interface
 * called RedirectResponseInterface from which the redirect URL can be obtained.
 *
 * How you do this redirect is up to your platform, code or framework at
 * this point.  For the below example I will assume that there is a
 * function called redirectTo() which can handle it for you.
 *
 * </code>
 *   if ($response->isRedirect()) {
 *       // Redirect the customer to PayPal so that they can sign in and
 *       // authorize the payment.
 *       echo "The transaction is a redirect";
 *       redirectTo($response->getRedirectUrl());
 *   }
 * </code>
 *
 * Step 4 is where the customer returns to your site.  This will happen on
 * either the returnUrl or the cancelUrl, that you provided in the purchase()
 * call.
 *
 * If the cancelUrl is called then you can assume that the customer has not
 * authorized the payment, therefore you can cancel the transaction.
 *
 * If the returnUrl is called, then you need to complete the transaction via
 * a further call to PayPal.
 *
 * Note this example assumes that the authorize has been successful.
 *
 * The payer ID and the payment ID returned from the callback after the authorize
 * will be passed to the return URL as GET parameters payerId and paymentId
 * respectively.
 *
 * <code>
 *   $paymentId = $_GET['paymentId'];
 *   $payerId = $_GET['payerId'];
 *
 *   // Once the transaction has been approved, we need to complete it.
 *   $transaction = $gateway->completePurchase(array(
 *       'payer_id'             => $payer_id,
 *       'transactionReference' => $sale_id,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       // The customer has successfully paid.
 *       echo "Step 4 was successful!\n";
 *   } else {
 *       // There was an error returned by completePurchase().  You should
 *       // check the error code and message from PayPal, which may be something
 *       // like "card declined", etc.
 *   }
 * </code>
 *
 * #### Note on Handling Error Messages
 *
 * PayPal account payments are a 2 step process.  Firstly the customer needs to
 * authorize the payment from PayPal to your application.  Secondly, assuming that
 * the customer does not have enough balance to pay the invoice from their PayPal
 * balance, PayPal needs to transfer the funds from the customer's credit card to
 * their PayPal account.  This transaction is between PayPal and the customer, and
 * not between the customer and you.
 *
 * If the second transaction fails then a call to completePurchase() will return
 * an error.  However this error message will be fairly generic.  For privacy
 * reasons, PayPal will not disclose to the merchant the full reason for the
 * failure, they will only disclose this to the customer.
 *
 * Therefore on a failed completeAuthorize() call you could display an error message
 * like this one:
 *
 * "PayPal failed to process the transaction from your card. For privacy reasons,
 * PayPal are unable to disclose to us the reason for this failure. You should try
 * a different payment method, a different card within PayPal, or contact PayPal
 * support if you need to understand the reason for the failed transaction. PayPal
 * may advise you to use a different card if the particular card is rejected
 * by the card issuer."
 *
 * @link https://developer.paypal.com/docs/integration/direct/capture-payment/#authorize-the-payment
 * @link https://developer.paypal.com/docs/api/#authorizations
 * @link http://bit.ly/1wUQ33R
 * @see RestCaptureRequest
 * @see RestPurchaseRequest
 */
class RestAuthorizeRequest extends AbstractRestRequest
{
    public function getData()
    {
        $data = array(
            'intent' => 'authorize',
            'payer' => array(
                'payment_method' => 'credit_card',
                'funding_instruments' => array()
            ),
            'transactions' => array(
                array(
                    'description' => $this->getDescription(),
                    'amount' => array(
                        'total' => $this->getAmount(),
                        'currency' => $this->getCurrency(),
                    ),
                    'invoice_number' => $this->getTransactionId()
                )
            ),
            'experience_profile_id' => $this->getExperienceProfileId()
        );

        $items = $this->getItems();
        if ($items) {
            $itemList = array();
            foreach ($items as $n => $item) {
                $itemList[] = array(
                    'name' => $item->getName(),
                    'description' => $item->getDescription(),
                    'quantity' => $item->getQuantity(),
                    'price' => $this->formatCurrency($item->getPrice()),
                    'currency' => $this->getCurrency()
                );
            }
            $data['transactions'][0]['item_list']["items"] = $itemList;
        }

        if ($this->getCardReference()) {
            $this->validate('amount');

            $data['payer']['funding_instruments'][] = array(
                'credit_card_token' => array(
                    'credit_card_id' => $this->getCardReference(),
                ),
            );
        } elseif ($this->getCard()) {
            $this->validate('amount', 'card');
            $this->getCard()->validate();

            $data['payer']['funding_instruments'][] = array(
                'credit_card' => array(
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
                )
            );

            // There's currently a quirk with the REST API that requires line2 to be
            // non-empty if it's present. Jul 14, 2014
            $line2 = $this->getCard()->getAddress2();
            if (!empty($line2)) {
                $data['payer']['funding_instruments'][0]['credit_card']['billing_address']['line2'] = $line2;
            }
        } else {
            $this->validate('amount', 'returnUrl', 'cancelUrl');

            unset($data['payer']['funding_instruments']);

            $data['payer']['payment_method'] = 'paypal';
            $data['redirect_urls'] = array(
                'return_url' => $this->getReturnUrl(),
                'cancel_url' => $this->getCancelUrl(),
            );
        }

        return $data;
    }

    /**
     * Get the experience profile id
     *
     * @return string
     */
    public function getExperienceProfileId()
    {
        return $this->getParameter('experienceProfileId');
    }

    /**
     * Set the experience profile id
     *
     * @param string $value
     * @return RestAuthorizeRequest provides a fluent interface.
     */
    public function setExperienceProfileId($value)
    {
        return $this->setParameter('experienceProfileId', $value);
    }

    /**
     * Get transaction description.
     *
     * The REST API does not currently have support for passing an invoice number
     * or transaction ID.
     *
     * @return string
     */
    public function getDescription()
    {
        $id = $this->getTransactionId();
        $desc = parent::getDescription();

        if (empty($id)) {
            return $desc;
        } elseif (empty($desc)) {
            return $id;
        } else {
            return "$id : $desc";
        }
    }

    /**
     * Get transaction endpoint.
     *
     * Authorization of payments is done using the /payment resource.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/payment';
    }

    protected function createResponse($data, $statusCode)
    {
        return $this->response = new RestAuthorizeResponse($this, $data, $statusCode);
    }
}
