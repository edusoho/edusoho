<?php

namespace Omnipay\Alipay;

use Omnipay\Alipay\Requests\AopTradeAppPayRequest;

/**
 * Class AopAppGateway
 * @package Omnipay\Alipay
 * @link    https://doc.open.alipay.com/docs/doc.htm?treeId=204&articleId=105051&docType=1
 */
class AopAppGateway extends AbstractAopGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay APP Gateway';
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\Alipay\Requests\AopTradeAppPayRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(AopTradeAppPayRequest::class, $parameters);
    }
}
