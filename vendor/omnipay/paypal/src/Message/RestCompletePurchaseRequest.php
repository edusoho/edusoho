<?php
/**
 * PayPal REST Complete Purchase Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Complete Purchase Request
 *
 * Use this message to execute (complete) a PayPal payment that has been
 * approved by the payer. You can optionally update transaction information
 * when executing the payment by passing in one or more transactions.
 *
 * This call only works after a buyer has approved the payment using the
 * provided PayPal approval URL.
 *
 * ### Example
 *
 * The payer ID and the payment ID returned from the callback after the purchase
 * will be passed to the return URL as GET parameters payerId and paymentId
 * respectively.
 *
 * See RestPurchaseRequest for the first part of this example transaction:
 *
 * <code>
 *   $paymentId = $_GET['paymentId'];
 *   $payerId = $_GET['payerId'];
 *
 *   // Once the transaction has been approved, we need to complete it.
 *   $transaction = $gateway->completePurchase(array(
 *       'payer_id'             => $payerId,
 *       'transactionReference' => $paymentId,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       // The customer has successfully paid.
 *   } else {
 *       // There was an error returned by completePurchase().  You should
 *       // check the error code and message from PayPal, which may be something
 *       // like "card declined", etc.
 *   }
 * </code>
 *
 * @see RestPurchaseRequest
 * @link https://developer.paypal.com/docs/api/#execute-an-approved-paypal-payment
 */
class RestCompletePurchaseRequest extends AbstractRestRequest
{
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        $this->validate('transactionReference', 'payerId');

        $data = array(
            'payer_id' => $this->getPayerId()
        );

        return $data;
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/payment/' . $this->getTransactionReference() . '/execute';
    }
}
