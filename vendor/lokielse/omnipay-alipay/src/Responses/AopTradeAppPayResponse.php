<?php

namespace Omnipay\Alipay\Responses;

class AopTradeAppPayResponse extends \Omnipay\Alipay\Responses\AbstractResponse
{
    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return true;
    }
    /**
     * Gets the redirect form data array, if the redirect method is POST.
     */
    public function getOrderString()
    {
        return $this->data['order_string'];
    }
}