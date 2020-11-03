<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Common\Message\RequestInterface;

/**
 * Response for Transaction Search request
 */
class ExpressTransactionSearchResponse extends Response
{
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        $payments = array();

        foreach ($this->data as $key => $value) {
            if ($this->isSuccessful()
                && preg_match('/(L_)?(?<key>[A-Za-z]+)(?<n>[0-9]+)/', $key, $matches)
            ) {
                $payments[$matches['n']][$matches['key']] = $value;
                unset($this->data[$key]);
            }
        }

        $this->data['payments'] = $payments;
    }

    public function getPayments()
    {
        return $this->data['payments'];
    }
}
