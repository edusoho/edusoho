<?php
/**
 * PayPal REST Update Plan Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Update Plan Request
 *
 * You can update the information for an existing billing plan. The state
 * of a plan must be active before a billing agreement is created.
 *
 * ### Request Data
 *
 * Pass the billing plan id in the URI of a PATCH call, including the replace
 * operation in the body. Other operations in the patch_request object will
 * throw validation exceptions.
 *
 * ### Example
 *
 * To create the billing plan, see the code example in RestCreatePlanRequest.
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
 *   // Update the billing plan
 *   $transaction = $gateway->updatePlan(array(
 *       'transactionReference'     => $plan_id,
 *       'state'                    => $gateway::BILLING_PLAN_STATE_ACTIVE,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Update Plan transaction was successful!\n";
 *   }
 * </code>
 *
 * ### Request Sample
 *
 * This is from the PayPal web site:
 *
 * <code>
 * curl -v -k -X PATCH 'https://api.sandbox.paypal.com/v1/payments/billing-plans/P-94458432VR012762KRWBZEUA' \
 *    -H "Content-Type: application/json" \
 *    -H "Authorization: Bearer <Access-Token>" \
 *    -d '[
 *        {
 *            "path": "/",
 *            "value": {
 *                "state": "ACTIVE"
 *            },
 *            "op": "replace"
 *        }
 *    ]'
 * </code>
 *
 * ### Response
 *
 * Returns the HTTP status of 200 if the call is successful.
 *
 * @link https://developer.paypal.com/docs/api/#update-a-plan
 * @see RestCreateSubscriptionRequest
 * @see Omnipay\PayPal\RestGateway
 */
class RestUpdatePlanRequest extends AbstractRestRequest
{
    /**
     * Get the plan state
     *
     * @return string
     */
    public function getState()
    {
        return $this->getParameter('state');
    }

    /**
     * Set the plan state
     *
     * @param string $value
     * @return RestUpdatePlanRequest provides a fluent interface.
     */
    public function setState($value)
    {
        return $this->setParameter('state', $value);
    }

    public function getData()
    {
        $this->validate('transactionReference', 'state');
        $data = array(array(
            'path'      => '/',
            'value'     => array(
                'state'     => $this->getState(),
            ),
            'op'        => 'replace'
        ));

        return $data;
    }

    /**
     * Get transaction endpoint.
     *
     * Billing plans are managed using the /billing-plans resource.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/billing-plans/' . $this->getTransactionReference();
    }

    protected function getHttpMethod()
    {
        return 'PATCH';
    }
}
