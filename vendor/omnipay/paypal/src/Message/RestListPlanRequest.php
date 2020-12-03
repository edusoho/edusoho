<?php
/**
 * PayPal REST List Plans Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST List Plans Request
 *
 * Use this call to get a list of plans in any state (CREATED, ACTIVE, etc.).
 * The plans returned are the plans made by the merchant making the call.
 *
 *
 * ### Example
 *
 * #### Initialize Gateway
 *
 * <code>
 *   // Create a gateway for the PayPal RestGateway
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('PayPal_Rest');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'clientId' => 'MyPayPalClientId',
 *       'secret'   => 'MyPayPalSecret',
 *       'testMode' => true, // Or false when you are ready for live transactions
 *   ));
 * </code>
 *
 * #### List all plans that have state CREATED
 * <code>
 *
 *   // List all billing plans
 *   $transaction = $gateway->listPlan([
 *       'state' => CREATED,
 *   ]);
 *   $response = $transaction->send();
 *   $data = $response->getData();
 *   echo "Gateway listPlan response data == " . print_r($data, true) . "\n";
 * </code>
 *
 * ### Request Sample
 *
 * This is from the PayPal web site:
 *
 * <code>
 * curl -v -X GET https://api.sandbox.paypal.com/v1/payments/billing-plans?page_size=3&status=ACTIVE&page=1\
 * -H "Content-Type:application/json" \
 * -H "Authorization: Bearer Access-Token"
 * </code>
 *
 * ### Response Sample
 *
 * This is from the PayPal web site:
 *
 * <code>
 * {
 * "total_items": "166",
 * "total_pages": "83",
 * "plans": [
 * {
 * "id": "P-7DC96732KA7763723UOPKETA",
 * "state": "ACTIVE",
 * "name": "Plan with Regular and Trial Payment Definitions",
 * "description": "Plan with regular and trial billing payment definitions.",
 * "type": "FIXED",
 * "create_time": "2017-08-22T04:41:52.836Z",
 * "update_time": "2017-08-22T04:41:53.169Z",
 * "links": [
 * {
 * "href": "https://api.sandbox.paypal.com//v1/payments/billing-plans/P-7DC96732KA7763723UOPKETA",
 * "rel": "self",
 * "method": "GET"
 * }
 * ]
 * },
 * {
 * "id": "P-1TV69435N82273154UPWDU4I",
 * "state": "ACTIVE",
 * "name": "Plan with Regular Payment Definition",
 * "description": "Plan with one regular payment definition, minimal merchant preferences, and no shipping fee",
 * "type": "INFINITE",
 * "create_time": "2017-08-22T04:41:55.623Z",
 * "update_time": "2017-08-22T04:41:56.055Z",
 * "links": [
 * {
 * "href": "https://api.sandbox.paypal.com//v1/payments/billing-plans/P-1TV69435N82273154UPWDU4I",
 * "rel": "self",
 * "method": "GET"
 * }
 * ]
 * }
 * ],
 * "links": [
 * {
 * "href": "https://api.sandbox.paypal.com/v1/payments/billing-plans?page_size=2&page=1&start=3&status=active",
 * "rel": "start",
 * "method": "GET"
 * },
 * {
 * "href": "https://api.sandbox.paypal.com/v1/payments/billing-plans?page_size=2&page=0&status=active",
 * "rel": "previous_page",
 * "method": "GET"
 * },
 * {
 * "href": "https://api.sandbox.paypal.com/v1/payments/billing-plans?page_size=2&page=2&status=active",
 * "rel": "next_page",
 * "method": "GET"
 * },
 * {
 * "href": "https://api.sandbox.paypal.com/v1/payments/billing-plans?page_size=2&page=82&status=active",
 * "rel": "last",
 * "method": "GET"
 * }
 * ]
 * }
 *
 * </code>
 *
 * @link https://developer.paypal.com/docs/api/payments.billing-plans#plan_list
 */
class RestListPlanRequest extends AbstractRestRequest
{
    /**
     *
     * Get the request page
     *
     * @return integer
     */

    public function getPage()
    {
        return $this->getParameter('page');
    }


    /**
     * Set the request page
     *
     * @param integer $value
     * @return AbstractRestRequest provides a fluent interface.
     */
    public function setPage($value)
    {
        return $this->setParameter('page', $value);
    }

    /**
     * Get the request status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getParameter('status');
    }

    /**
     * Set the request status
     *
     * @param string $value
     * @return AbstractRestRequest provides a fluent interface.
     */
    public function setStatus($value)
    {
        return $this->setParameter('status', $value);
    }

    /**
     * Get the request page size
     *
     * @return string
     */
    public function getPageSize()
    {
        return $this->getParameter('pageSize');
    }

    /**
     * Set the request page size
     *
     * @param string $value
     * @return AbstractRestRequest provides a fluent interface.
     */
    public function setPageSize($value)
    {
        return $this->setParameter('pageSize', $value);
    }

    /**
     * Get the request total required
     *
     * @return string
     */
    public function getTotalRequired()
    {
        return $this->getParameter('totalRequired');
    }

    /**
     * Set the request total required
     *
     * @param string $value
     * @return AbstractRestRequest provides a fluent interface.
     */
    public function setTotalRequired($value)
    {
        return $this->setParameter('totalRequired', $value);
    }




    public function getData()
    {
        return array(
            'page'             => $this->getPage(),
            'status'          => $this->getStatus(),
            'page_size'       => $this->getPageSize(),
            'total_required'        => $this->getTotalRequired()
        );
    }

    /**
     * Get HTTP Method.
     *
     * The HTTP method for list plans requests must be GET.
     *
     * @return string
     */
    protected function getHttpMethod()
    {
        return 'GET';
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/billing-plans';
    }
}
