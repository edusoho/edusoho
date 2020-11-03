<?php
/**
 * PayPal REST Fetch Purchase Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Fetch Purchase Request
 *
 * Use this call to get details about payments that have not completed, such
 * as payments that are created and approved, or if a payment has failed.
 *
 * ### Example
 *
 * See RestPurchaseRequest for the first part of this example transaction:
 *
 * <code>
 *   // Fetch the transaction so that details can be found for refund, etc.
 *   $transaction = $gateway->fetchPurchase();
 *   $transaction->setTransactionReference($sale_id);
 *   $response = $transaction->send();
 *   $data = $response->getData();
 *   echo "Gateway fetchTransaction response data == " . print_r($data, true) . "\n";
 * </code>
 *
 * @see RestPurchaseRequest
 * @link https://developer.paypal.com/docs/api/#look-up-a-payment-resource
 */
class RestFetchPurchaseRequest extends AbstractRestRequest
{
    public function getData()
    {
        $this->validate('transactionReference');
        return array();
    }

    /**
     * Get HTTP Method.
     *
     * The HTTP method for fetchTransaction requests must be GET.
     * Using POST results in an error 500 from PayPal.
     *
     * @return string
     */
    protected function getHttpMethod()
    {
        return 'GET';
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/payment/' . $this->getTransactionReference();
    }
}
