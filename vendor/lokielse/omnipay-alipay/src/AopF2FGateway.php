<?php

namespace Omnipay\Alipay;

use Omnipay\Alipay\Requests\AopTradePayRequest;
use Omnipay\Alipay\Requests\AopTradePreCreateRequest;

/**
 * Class AopF2FGateway
 * @package Omnipay\Alipay
 * @link https://doc.open.alipay.com/docs/doc.htm?treeId=194&articleId=105072&docType=1
 */
class AopF2FGateway extends AbstractAopGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay Face To Face Gateway';
    }


    /**
     * @param array $parameters
     *
     * @return AopTradePayRequest
     */
    public function capture(array $parameters = [])
    {
        return $this->createRequest(AopTradePayRequest::class, $parameters);
    }


    /**
     * @param array $parameters
     *
     * @return AopTradePreCreateRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(AopTradePreCreateRequest::class, $parameters);
    }
}
