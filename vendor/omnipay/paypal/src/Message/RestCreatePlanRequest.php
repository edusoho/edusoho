<?php
/**
 * PayPal REST Create Plan Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Create Plan Request
 *
 * PayPal offers merchants a /billing-plans resource for providing billing plans
 * to users for recurring payments.
 *
 * After the billing plan is created, the /billing-agreements resource provides
 * billing agreements so that users can agree to be billed for the plans.
 *
 * You can create an empty billing plan and add a trial period and/or regular
 * billing. Alternatively, you can create a fully loaded plan that includes both
 * a trial period and regular billing. Note: By default, a created billing plan
 * is in a CREATED state. A user cannot subscribe to the billing plan unless it
 * has been set to the ACTIVE state.
 *
 * ### Request Data
 *
 * In order to create a new billing plan you must submit the following details:
 *
 * * name (string). Required.
 * * description (string). Required.
 * * type (string). Allowed values: FIXED, INFINITE. Required.
 * * payment_definitions (array)
 * * merchant_preferences (object)
 *
 * ### Example
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
 *   // Do a create plan transaction on the gateway
 *   $transaction = $gateway->createPlan(array(
 *       'name'                     => 'Test Plan',
 *       'description'              => 'A plan created for testing',
 *       'type'                     => $gateway::BILLING_PLAN_TYPE_FIXED,
 *       'paymentDefinitions'       => [
 *           [
 *               'name'                 => 'Monthly Payments for 12 months',
 *               'type'                 => $gateway::PAYMENT_TRIAL,
 *               'frequency'            => $gateway::BILLING_PLAN_FREQUENCY_MONTH,
 *               'frequency_interval'   => 1,
 *               'cycles'               => 12,
 *               'amount'               => ['value' => 10.00, 'currency' => 'USD'],
 *           ],
 *       ],
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Create Plan transaction was successful!\n";
 *       $plan_id = $response->getTransactionReference();
 *       echo "Plan reference = " . $plan_id . "\n";
 *   }
 * </code>
 *
 * ### Request Sample
 *
 * This is from the PayPal web site:
 *
 * <code>
 * curl -v POST https://api.sandbox.paypal.com/v1/payments/billing-plans \
 * -H 'Content-Type:application/json' \
 * -H 'Authorization: Bearer <Access-Token>' \
 * -d '{
 *     "name": "T-Shirt of the Month Club Plan",
 *     "description": "Template creation.",
 *     "type": "fixed",
 *     "payment_definitions": [
 *         {
 *             "name": "Regular Payments",
 *             "type": "REGULAR",
 *             "frequency": "MONTH",
 *             "frequency_interval": "2",
 *             "amount": {
 *                 "value": "100",
 *                 "currency": "USD"
 *             },
 *             "cycles": "12",
 *             "charge_models": [
 *                 {
 *                     "type": "SHIPPING",
 *                     "amount": {
 *                         "value": "10",
 *                         "currency": "USD"
 *                     }
 *                 },
 *                 {
 *                     "type": "TAX",
 *                     "amount": {
 *                         "value": "12",
 *                         "currency": "USD"
 *                     }
 *                 }
 *             ]
 *         }
 *     ],
 *     "merchant_preferences": {
 *         "setup_fee": {
 *             "value": "1",
 *             "currency": "USD"
 *         },
 *         "return_url": "http://www.return.com",
 *         "cancel_url": "http://www.cancel.com",
 *         "auto_bill_amount": "YES",
 *         "initial_fail_amount_action": "CONTINUE",
 *         "max_fail_attempts": "0"
 *     }
 * }'
 * </code>
 *
 * ### Response Sample
 *
 * This is from the PayPal web site:
 *
 * <code>
 * {
 *     "id": "P-94458432VR012762KRWBZEUA",
 *     "state": "CREATED",
 *     "name": "T-Shirt of the Month Club Plan",
 *     "description": "Template creation.",
 *     "type": "FIXED",
 *     "payment_definitions": [
 *         {
 *             "id": "PD-50606817NF8063316RWBZEUA",
 *             "name": "Regular Payments",
 *             "type": "REGULAR",
 *             "frequency": "Month",
 *             "amount": {
 *                 "currency": "USD",
 *                 "value": "100"
 *             },
 *             "charge_models": [
 *                 {
 *                     "id": "CHM-55M5618301871492MRWBZEUA",
 *                     "type": "SHIPPING",
 *                     "amount": {
 *                         "currency": "USD",
 *                         "value": "10"
 *                     }
 *                 },
 *                 {
 *                     "id": "CHM-92S85978TN737850VRWBZEUA",
 *                     "type": "TAX",
 *                     "amount": {
 *                         "currency": "USD",
 *                         "value": "12"
 *                     }
 *                 }
 *             ],
 *             "cycles": "12",
 *             "frequency_interval": "2"
 *         }
 *     ],
 *     "merchant_preferences": {
 *         "setup_fee": {
 *             "currency": "USD",
 *             "value": "1"
 *         },
 *         "max_fail_attempts": "0",
 *         "return_url": "http://www.return.com",
 *         "cancel_url": "http://www.cancel.com",
 *         "auto_bill_amount": "YES",
 *         "initial_fail_amount_action": "CONTINUE"
 *     },
 *     "create_time": "2014-07-31T17:41:55.920Z",
 *     "update_time": "2014-07-31T17:41:55.920Z",
 *     "links": [
 *         {
 *             "href": "https://api.sandbox.paypal.com/v1/payments/billing-plans/P-94458432VR012762KRWBZEUA",
 *             "rel": "self",
 *             "method": "GET"
 *         }
 *     ]
 * }
 * </code>
 *
 * @link https://developer.paypal.com/docs/api/#create-a-plan
 * @see Omnipay\PayPal\RestGateway
 */
class RestCreatePlanRequest extends AbstractRestRequest
{
    /**
     * Get the plan name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getParameter('name');
    }

    /**
     * Set the plan name
     *
     * @param string $value
     * @return RestCreatePlanRequest provides a fluent interface.
     */
    public function setName($value)
    {
        return $this->setParameter('name', $value);
    }

    /**
     * Get the plan type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getParameter('type');
    }

    /**
     * Set the plan type
     *
     * @param string $value either RestGateway::BILLING_PLAN_TYPE_FIXED
     *                      or RestGateway::BILLING_PLAN_TYPE_INFINITE
     * @return RestCreatePlanRequest provides a fluent interface.
     */
    public function setType($value)
    {
        return $this->setParameter('type', $value);
    }

    /**
     * Get the plan payment definitions
     *
     * See the class documentation and the PayPal REST API documentation for
     * a description of the array elements.
     *
     * @return array
     * @link https://developer.paypal.com/docs/api/#paymentdefinition-object
     */
    public function getPaymentDefinitions()
    {
        return $this->getParameter('paymentDefinitions');
    }

    /**
     * Set the plan payment definitions
     *
     * See the class documentation and the PayPal REST API documentation for
     * a description of the array elements.
     *
     * @param array $value
     * @return RestCreatePlanRequest provides a fluent interface.
     * @link https://developer.paypal.com/docs/api/#paymentdefinition-object
     */
    public function setPaymentDefinitions(array $value)
    {
        return $this->setParameter('paymentDefinitions', $value);
    }

    /**
     * Get the plan merchant preferences
     *
     * See the class documentation and the PayPal REST API documentation for
     * a description of the array elements.
     *
     * @return array
     * @link https://developer.paypal.com/docs/api/#merchantpreferences-object
     */
    public function getMerchantPreferences()
    {
        return $this->getParameter('merchantPreferences');
    }

    /**
     * Set the plan merchant preferences
     *
     * See the class documentation and the PayPal REST API documentation for
     * a description of the array elements.
     *
     * @param array $value
     * @return RestCreatePlanRequest provides a fluent interface.
     * @link https://developer.paypal.com/docs/api/#merchantpreferences-object
     */
    public function setMerchantPreferences(array $value)
    {
        return $this->setParameter('merchantPreferences', $value);
    }

    public function getData()
    {
        $this->validate('name', 'description', 'type');
        $data = array(
            'name'                  => $this->getName(),
            'description'           => $this->getDescription(),
            'type'                  => $this->getType(),
            'payment_definitions'   => $this->getPaymentDefinitions(),
            'merchant_preferences'  => $this->getMerchantPreferences(),
        );

        return $data;
    }

    /**
     * Get transaction endpoint.
     *
     * Billing plans are created using the /billing-plans resource.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/billing-plans';
    }
}
