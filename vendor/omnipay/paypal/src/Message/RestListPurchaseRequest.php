<?php
/**
 * PayPal REST List Purchase Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST List Purchase Request
 *
 * Use this call to get a list of payments in any state (created, approved,
 * failed, etc.). The payments returned are the payments made to the merchant
 * making the call.
 *
 * ### Example
 *
 * See RestPurchaseRequest for the first part of this example transaction:
 *
 * <code>
 *   // Make some DateTimes for start and end times
 *   $start_time = new \DateTime('yesterday');
 *   $end_time = new \DateTime('now');
 *
 *   // List the transaction so that details can be found for refund, etc.
 *   $transaction = $gateway->listPurchase(
 *       'startTime' => $start_time,
 *       'endTime    => $end_time
 *   );
 *   $response = $transaction->send();
 *   $data = $response->getData();
 *   echo "Gateway listPurchase response data == " . print_r($data, true) . "\n";
 * </code>
 *
 * ### Request Sample
 *
 * This is from the PayPal web site:
 *
 * <code>
 * curl -v -X GET https://api.sandbox.paypal.com/v1/payments/payment?
 *     sort_order=asc&sort_by=update_time \
 *     -H "Content-Type:application/json" \
 *     -H "Authorization: Bearer <Access-Token>"
 * </code>
 *
 * ### Response Sample
 *
 * This is from the PayPal web site:
 *
 * <code>
 * {
 *     "payments": [
 *         {
 *             "id": "PAY-4D099447DD202993VKEFMRJQ",
 *             "create_time": "2013-01-31T19:40:22Z",
 *             "update_time": "2013-01-31T19:40:24Z",
 *             "state": "approved",
 *             "intent": "sale",
 *             "payer": {
 *                 "payment_method": "credit_card",
 *                 "funding_instruments": [
 *                     {
 *                         "credit_card": {
 *                             "type": "visa",
 *                             "number": "xxxxxxxxxxxx0331",
 *                             "expire_month": "10",
 *                             "expire_year": "2018",
 *                             "first_name": "Betsy",
 *                             "last_name": "Buyer",
 *                             "billing_address": {
 *                                 "line1": "111 First Street",
 *                                 "city": "Saratoga",
 *                                 "state": "CA",
 *                                 "postal_code": "95070",
 *                                 "country_code": "US"
 *                             }
 *                         }
 *                     }
 *                 ]
 *             },
 *             "transactions": [
 *                 {
 *                     "amount": {
 *                         "total": "110.54",
 *                         "currency": "USD"
 *                     },
 *                     "description": "This is the payment transaction description.",
 *                     "related_resources": [
 *                         {
 *                             "sale": {
 *                                 "id": "1D971400A7097562W",
 *                                 "create_time": "2013-01-31T19:40:23Z",
 *                                 "update_time": "2013-01-31T19:40:25Z",
 *                                 "state": "completed",
 *                                 "amount": {
 *                                     "total": "110.54",
 *                                     "currency": "USD"
 *                                 },
 *                                 "parent_payment": "PAY-4D099447DD202993VKEFMRJQ",
 *                                 "links": [
 *                                     {
 *                                         "href":
 * "https://api.sandbox.paypal.com/v1/payments/sale/1D971400A7097562W",
 *                                         "rel": "self",
 *                                         "method": "GET"
 *                                     },
 *                                     {
 *                                         "href":
 * "https://api.sandbox.paypal.com/v1/payments/sale/1D971400A7097562W/refund",
 *                                         "rel": "refund",
 *                                         "method": "POST"
 *                                     },
 *                                     {
 *                                         "href":
 * "https://api.sandbox.paypal.com/v1/payments/payment/PAY-4D099447DD202993VKEFMRJQ",
 *                                         "rel": "parent_payment",
 *                                         "method": "GET"
 *                                     }
 *                                 ]
 *                             }
 *                         }
 *                     ]
 *                 }
 *             ],
 *             "links": [
 *                          {
 *                              "href":
 * "https://api.sandbox.paypal.com/v1/payments/payment/PAY-4D099447DD202993VKEFMRJQ",
 *                              "rel": "self",
 *                              "method": "GET"
 *                          }
 *                      ]
 *         }
 *     ]
 * }
 * </code>
 *
 * @see RestPurchaseRequest
 * @link https://developer.paypal.com/docs/api/#list-payment-resources
 */
class RestListPurchaseRequest extends AbstractRestRequest
{
    /**
     * Get the request count
     *
     * @return integer
     */
    public function getCount()
    {
        return $this->getParameter('count');
    }

    /**
     * Set the request count
     *
     * @param integer $value
     * @return AbstractRestRequest provides a fluent interface.
     */
    public function setCount($value)
    {
        return $this->setParameter('count', $value);
    }

    /**
     * Get the request startId
     *
     * @return string
     */
    public function getStartId()
    {
        return $this->getParameter('startId');
    }

    /**
     * Set the request startId
     *
     * @param string $value
     * @return AbstractRestRequest provides a fluent interface.
     */
    public function setStartId($value)
    {
        return $this->setParameter('startId', $value);
    }

    /**
     * Get the request startIndex
     *
     * @return integer
     */
    public function getStartIndex()
    {
        return $this->getParameter('startIndex');
    }

    /**
     * Set the request startIndex
     *
     * @param integer $value
     * @return AbstractRestRequest provides a fluent interface.
     */
    public function setStartIndex($value)
    {
        return $this->setParameter('startIndex', $value);
    }

    /**
     * Get the request startTime
     *
     * @return string
     */
    public function getStartTime()
    {
        return $this->getParameter('startTime');
    }

    /**
     * Set the request startTime
     *
     * @param string|\DateTime $value
     * @return AbstractRestRequest provides a fluent interface.
     */
    public function setStartTime($value)
    {
        if ($value instanceof \DateTime) {
            $value->setTimezone(new \DateTimeZone('UTC'));
            $value = $value->format('Y-m-d\TH:i:s\Z');
        }
        return $this->setParameter('startTime', $value);
    }

    /**
     * Get the request endTime
     *
     * @return string
     */
    public function getEndTime()
    {
        return $this->getParameter('endTime');
    }

    /**
     * Set the request endTime
     *
     * @param string|\DateTime $value
     * @return AbstractRestRequest provides a fluent interface.
     */
    public function setEndTime($value)
    {
        if ($value instanceof \DateTime) {
            $value->setTimezone(new \DateTimeZone('UTC'));
            $value = $value->format('Y-m-d\TH:i:s\Z');
        }
        return $this->setParameter('endTime', $value);
    }

    public function getData()
    {
        return array(
            'count'             => $this->getCount(),
            'start_id'          => $this->getStartId(),
            'start_index'       => $this->getStartIndex(),
            'start_time'        => $this->getStartTime(),
            'end_time'          => $this->getEndTime(),
        );
    }

    /**
     * Get HTTP Method.
     *
     * The HTTP method for listPurchase requests must be GET.
     *
     * @return string
     */
    protected function getHttpMethod()
    {
        return 'GET';
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/payment';
    }
}
