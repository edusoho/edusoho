<?php
/**
 * PayPal REST Fetch Transaction Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Fetch Transaction Request
 *
 * To get details about completed payments (sale transaction) created by a payment request
 * or to refund a direct sale transaction, PayPal provides the /sale resource and related
 * sub-resources.
 *
 * Example -- note this example assumes that the purchase has been successful
 * and that the transaction ID returned from the purchase is held in $sale_id.
 * See RestPurchaseRequest for the first part of this example transaction:
 *
 * <code>
 *   // Fetch the transaction so that details can be found for refund, etc.
 *   $transaction = $gateway->fetchTransaction();
 *   $transaction->setTransactionReference($sale_id);
 *   $response = $transaction->send();
 *   $data = $response->getData();
 *   echo "Gateway fetchTransaction response data == " . print_r($data, true) . "\n";
 * </code>
 *
 * @see RestPurchaseRequest
 * @link https://developer.paypal.com/docs/api/#sale-transactions
 */
class RestFetchTransactionRequest extends AbstractRestRequest
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
        return parent::getEndpoint() . '/payments/sale/' . $this->getTransactionReference();
    }
}
