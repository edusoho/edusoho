<?php

namespace Omnipay\PayPal\Message;

/**
 * PayPal Express Complete Payment Response
 */
class ExpressCompletePurchaseResponse extends ExpressAuthorizeResponse
{
    /*
     * Is this complete purchase response successful? Will not be successful if it's a redirect response.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        $success = isset($this->data['ACK']) && in_array($this->data['ACK'], array('Success', 'SuccessWithWarning'));
        return !$this->isRedirect() && $success;
    }

    /**
     * The complete purchase response can be in error where it wants to have your customer return to paypal.
     *
     * @return bool
     */
    public function isRedirect()
    {
        return isset($this->data['L_ERRORCODE0']) && in_array($this->data['L_ERRORCODE0'], array('10486'));
    }

    /**
     * The transaction reference obtained from the purchase() call can't be used to refund a purchase.
     *
     * @return string
     */
    public function getTransactionReference()
    {
        if ($this->isSuccessful() && isset($this->data['PAYMENTINFO_0_TRANSACTIONID'])) {
            return $this->data['PAYMENTINFO_0_TRANSACTIONID'];
        }

        return parent::getTransactionReference();
    }
}
