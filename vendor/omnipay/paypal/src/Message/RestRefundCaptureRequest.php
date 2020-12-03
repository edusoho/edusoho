<?php
/**
 * PayPal REST Refund Captured Payment Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Refund Captured Payment Request
 *
 * Use this call to refund a captured payment.
 *
 * @link https://developer.paypal.com/docs/api/#refund-a-captured-payment
 * @see RestAuthorizeRequest
 * @see RestCaptureRequest
 */
class RestRefundCaptureRequest extends AbstractRestRequest
{
    public function getData()
    {
        $this->validate('transactionReference');

        return array(
            'amount' => array(
                'currency' => $this->getCurrency(),
                'total' => $this->getAmount(),
            ),
            'description' => $this->getDescription(),
        );
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/capture/' . $this->getTransactionReference() . '/refund';
    }
}
