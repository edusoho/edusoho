<?php
/**
 * PayPal REST Void an authorization
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Void an authorization
 *
 * Use this call to void a previously authorized payment.
 * Note: A fully captured authorization cannot be voided.
 *
 * @link https://developer.paypal.com/docs/api/#void-an-authorization
 * @see RestAuthorizeRequest
 */
class RestVoidRequest extends AbstractRestRequest
{
    public function getData()
    {
        $this->validate('transactionReference');
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/authorization/' . $this->getTransactionReference() . '/void';
    }
}
