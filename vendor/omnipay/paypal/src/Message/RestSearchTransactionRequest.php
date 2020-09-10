<?php
/**
 * PayPal REST Search Transaction Request
 */

namespace Omnipay\PayPal\Message;

/**
 * PayPal REST Search Transaction Request
 *
 * Use this call to search for the transactions within a billing agreement.
 * Note that this is not a generic transaction search function -- for that
 * see RestListPurchaseRequest.  It only searches for transactions within
 * a billing agreement.
 *
 * This should be used on a regular basis to determine the success / failure
 * state of transactions on active billing agreements.
 *
 * ### Example
 *
 * <code>
 *   // List the transactions for a billing agreement.
 *   $transaction = $gateway->listPurchase();
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
 * curl -v GET https://api.sandbox.paypal.com/v1/payments/billing-agreements/I-0LN988D3JACS/transactions \
 *     -H 'Content-Type:application/json' \
 *     -H 'Authorization: Bearer <Access-Token>'
 * </code>
 *
 * ### Response Sample
 *
 * This is from the PayPal web site:
 *
 * <code>
 * {
 *     "agreement_transaction_list": [
 *         {
 *             "transaction_id": "I-0LN988D3JACS",
 *             "status": "Created",
 *             "transaction_type": "Recurring Payment",
 *             "payer_email": "bbuyer@example.com",
 *             "payer_name": "Betsy Buyer",
 *             "time_stamp": "2014-06-09T09:29:36Z",
 *             "time_zone": "GMT"
 *         },
 *         {
 *             "transaction_id": "928415314Y5640008",
 *             "status": "Completed",
 *             "transaction_type": "Recurring Payment",
 *             "amount": {
 *                 "currency": "USD",
 *                 "value": "1.00"
 *             },
 *             "fee_amount": {
 *                 "currency": "USD",
 *                 "value": "-0.33"
 *             },
 *             "net_amount": {
 *                 "currency": "USD",
 *                 "value": "0.67"
 *             },
 *             "payer_email": "bbuyer@example.com",
 *             "payer_name": "Betsy Buyer",
 *             "time_stamp": "2014-06-09T09:42:47Z",
 *             "time_zone": "GMT"
 *         },
 *         {
 *             "transaction_id": "I-0LN988D3JACS",
 *             "status": "Suspended",
 *             "transaction_type": "Recurring Payment",
 *             "payer_email": "bbuyer@example.com",
 *             "payer_name": "Betsy Buyer",
 *             "time_stamp": "2014-06-09T11:18:34Z",
 *             "time_zone": "GMT"
 *         },
 *         {
 *             "transaction_id": "I-0LN988D3JACS",
 *             "status": "Reactivated",
 *             "transaction_type": "Recurring Payment",
 *             "payer_email": "bbuyer@example.com",
 *             "payer_name": "Betsy Buyer",
 *             "time_stamp": "2014-06-09T11:18:48Z",
 *             "time_zone": "GMT"
 *         }
 *     ]
 * }
 * </code>
 *
 * ### Known Issues
 *
 * PayPal subscription payments cannot be refunded. PayPal is working on this functionality
 * for their future API release.  In order to refund a PayPal subscription payment, you will
 * need to use the PayPal web interface to refund it manually.
 *
 * @see RestCreateSubscriptionRequest
 * @link https://developer.paypal.com/docs/api/#search-for-transactions
 */
class RestSearchTransactionRequest extends AbstractRestRequest
{
    /**
     * Get the agreement ID
     *
     * @return string
     */
    public function getAgreementId()
    {
        return $this->getParameter('agreementId');
    }

    /**
     * Set the agreement ID
     *
     * @param string $value
     * @return RestSearchTransactionRequest provides a fluent interface.
     */
    public function setAgreementId($value)
    {
        return $this->setParameter('agreementId', $value);
    }

    /**
     * Get the request startDate
     *
     * @return string
     */
    public function getStartDate()
    {
        return $this->getParameter('startDate');
    }

    /**
     * Set the request startDate
     *
     * @param string|DateTime $value
     * @return RestSearchTransactionRequest provides a fluent interface.
     */
    public function setStartDate($value)
    {
        return $this->setParameter('startDate', is_string($value) ? new \DateTime($value) : $value);
    }

    /**
     * Get the request endDate
     *
     * @return string
     */
    public function getEndDate()
    {
        return $this->getParameter('endDate');
    }

    /**
     * Set the request endDate
     *
     * @param string|DateTime $value
     * @return RestSearchTransactionRequest provides a fluent interface.
     */
    public function setEndDate($value)
    {
        return $this->setParameter('endDate', is_string($value) ? new \DateTime($value) : $value);
    }

    public function getData()
    {
        $this->validate('agreementId', 'startDate', 'endDate');
        return array(
            'start_date' => $this->getStartDate()->format('Y-m-d'),
            'end_date'   => $this->getEndDate()->format('Y-m-d'),
        );
    }

    /**
     * Get HTTP Method.
     *
     * The HTTP method for searchTransaction requests must be GET.
     *
     * @return string
     */
    protected function getHttpMethod()
    {
        return 'GET';
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/billing-agreements/' .
            $this->getAgreementId() . '/transactions';
    }
}
