<?php

namespace Omnipay\Alipay\Responses;

class LegacyQueryResponse extends \Omnipay\Alipay\Responses\AbstractLegacyResponse
{
    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->data['is_success'] === 'T';
    }
    /**
     * Is the trade paid?
     *
     * @return boolean
     */
    public function isPaid()
    {
        $response = array_get($this->data, 'response');
        if ($response) {
            $trade = array_get($response, 'trade');
            if (array_get($trade, 'trade_status')) {
                if (array_get($trade, 'trade_status') == 'TRADE_SUCCESS') {
                    return true;
                } elseif (array_get($trade, 'trade_status') == 'TRADE_FINISHED') {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}