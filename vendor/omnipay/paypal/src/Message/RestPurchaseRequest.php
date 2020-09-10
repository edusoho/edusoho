<?php
/**
 * PayPal REST Purchase Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Purchase Request
 *
 * PayPal provides various payment related operations using
 * the /payment resource and related sub-resources. Use payment
 * for direct credit card payments and PayPal account payments.
 * You can also use sub-resources to get payment related details.
 *
 * Note that a PayPal Purchase Request looks exactly like a PayPal
 * Authorize request except that the 'intent' is set to 'sale' for
 * immediate payment.  This class takes advantage of that by
 * extending the RestAuthorizeRequest class and simply over-riding
 * the getData() function to set the intent to sale.
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
 * #### Direct Credit Card Payment
 *
 * This is for the use case where a customer has presented their
 * credit card details and you intend to use the PayPal REST gateway
 * for processing a transaction using that credit card data.
 *
 * This does not require the customer to have a PayPal account.
 *
 * <code>
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
 *   // Do a purchase transaction on the gateway
 *   try {
 *       $transaction = $gateway->purchase(array(
 *           'amount'        => '10.00',
 *           'currency'      => 'AUD',
 *           'description'   => 'This is a test purchase transaction.',
 *           'card'          => $card,
 *       ));
 *       $response = $transaction->send();
 *       $data = $response->getData();
 *       echo "Gateway purchase response data == " . print_r($data, true) . "\n";
 *
 *       if ($response->isSuccessful()) {
 *           echo "Purchase transaction was successful!\n";
 *       }
 *   } catch (\Exception $e) {
 *       echo "Exception caught while attempting authorize.\n";
 *       echo "Exception type == " . get_class($e) . "\n";
 *       echo "Message == " . $e->getMessage() . "\n";
 *   }
 * </code>
 *
 * Direct credit card payment and related features are restricted in
 * some countries.
 * As of January 2015 these transactions are only supported in the UK
 * and in the USA.
 *
 * #### PayPal Account Payment
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
 * <code>
 *   // Do a purchase transaction on the gateway
 *   try {
 *       $transaction = $gateway->purchase(array(
 *           'amount'        => '10.00',
 *           'currency'      => 'AUD',
 *           'description'   => 'This is a test purchase transaction.',
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
 *       echo "Exception caught while attempting purchase.\n";
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
 * <code>
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
 * Note this example assumes that the purchase has been successful.
 *
 * The payer ID and the payment ID returned from the callback after the purchase
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
 * Therefore on a failed completePurchase() call you could display an error message
 * like this one:
 *
 * "PayPal failed to process the transaction from your card. For privacy reasons,
 * PayPal are unable to disclose to us the reason for this failure. You should try
 * a different payment method, a different card within PayPal, or contact PayPal
 * support if you need to understand the reason for the failed transaction. PayPal
 * may advise you to use a different card if the particular card is rejected
 * by the card issuer."
 *
 * @link https://developer.paypal.com/docs/api/#create-a-payment
 * @see RestAuthorizeRequest
 */
class RestPurchaseRequest extends RestAuthorizeRequest
{
    public function getData()
    {
        $data = parent::getData();
        $data['intent'] = 'sale';
        return $data;
    }
}
