<?php
/**
 * PayPal REST Complete Subscription Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Complete Subscription Request
 *
 * Use this call to execute an agreement after the buyer approves it.
 *
 * Note: This request is only necessary for PayPal payments. Billing
 * agreements for credit card payments execute automatically at the time
 * of creation and so this request is not necessary for credit card payments.
 *
 * ### Request Data
 *
 * Pass the token in the URI of a POST call to execute the subscription
 * agreement after buyer approval. You can find the token in the execute
 * link returned by the request to create a billing agreement.
 *
 * No other data is required.
 *
 * ### Example
 *
 * To create the agreement, see the code example in RestCreateSubscriptionRequest.
 *
 * At the completion of a createSubscription call, the customer should be
 * redirected to the redirect URL contained in $response->getRedirectUrl().  Once
 * the customer has approved the agreement and be returned to the returnUrl
 * in the call.  The returnUrl can contain the following code to complete
 * the agreement:
 *
 * <code>
 *   // Create a gateway for the PayPal REST Gateway
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
 *   // Do a complete subscription transaction on the gateway
 *   $transaction = $gateway->completeSubscription(array(
 *       'transactionReference'     => $subscription_id,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Complete Subscription transaction was successful!\n";
 *       $subscription_id = $response->getTransactionReference();
 *       echo "Subscription reference = " . $subscription_id;
 *   }
 * </code>
 *
 * Note that the subscription_id that you get from calling the response's
 * getTransactionReference() method at the end of the completeSubscription
 * call will be different to the one that you got after calling the response's
 * getTransactionReference() method at the end of the createSubscription
 * call.  The one that you get from completeSubscription is the correct
 * one to use going forwards (e.g. for cancelling or updating the subscription).
 *
 * ### Request Sample
 *
 * This is from the PayPal web site:
 *
 * <code>
 * curl -v POST https://api.sandbox.paypal.com/v1/payments/billing-agreements/EC-0JP008296V451950C/agreement-execute \
 *     -H 'Content-Type:application/json' \
 *     -H 'Authorization: Bearer <Access-Token>' \
 *     -d '{}'
 * </code>
 *
 * ### Response Sample
 *
 * This is from the PayPal web site:
 *
 * <code>
 * {
 *     "id": "I-0LN988D3JACS",
 *     "links": [
 *         {
 *             "href": "https://api.sandbox.paypal.com/v1/payments/billing-agreements/I-0LN988D3JACS",
 *             "rel": "self",
 *             "method": "GET"
 *         }
 *     ]
 * }
 * </code>
 *
 * @link https://developer.paypal.com/docs/api/#execute-an-agreement
 * @see RestCreateSubscriptionRequest
 * @see Omnipay\PayPal\RestGateway
 */
class RestCompleteSubscriptionRequest extends AbstractRestRequest
{
    public function getData()
    {
        $this->validate('transactionReference');
        $data = array();

        return $data;
    }

    /**
     * Get transaction endpoint.
     *
     * Subscriptions are executed using the /billing-agreements resource.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/billing-agreements/' .
            $this->getTransactionReference() . '/agreement-execute';
    }
}
