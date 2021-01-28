<?php
/**
 * PayPal REST Delete Card Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Delete Card Request
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
 * Example.  This example assumes that the card has already been created
 * using a RestCreateCardRequest call and that the card ID has been stored
 * in $card_id.  See RestCreateCardRequest for the details of the first
 * part of this process.
 *
 * <code>
 *   $transaction = $gateway->deleteCard();
 *   $transaction->setCardReference($card_id);
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Gateway deleteCard was successful.\n";
 *   } else {
 *       echo "Gateway deleteCard failed.\n";
 *   }
 * </code>
 *
 * @link https://developer.paypal.com/docs/api/#vault
 * @link https://developer.paypal.com/docs/api/#delete-a-stored-credit-card
 * @link http://bit.ly/1wUQ33R
 * @see RestCreateCardRequest
 */
class RestDeleteCardRequest extends AbstractRestRequest
{
    public function getHttpMethod()
    {
        return 'DELETE';
    }

    public function getData()
    {
        $this->validate('cardReference');
        return array();
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/vault/credit-cards/' . $this->getCardReference();
    }
}
