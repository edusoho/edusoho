<?php

namespace Omnipay\Alipay;

use Omnipay\Alipay\Requests\AopTradeCreateRequest;

/**
 * Class AopJsGateway
 * @package Omnipay\Alipay
 * @link    https://docs.open.alipay.com/api_1/alipay.trade.create
 * @link    https://myjsapi.alipay.com/jsapi/native/trade-pay.html
 */
class AopJsGateway extends AbstractAopGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay Js Gateway';
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(AopTradeCreateRequest::class, $parameters);
    }
}
