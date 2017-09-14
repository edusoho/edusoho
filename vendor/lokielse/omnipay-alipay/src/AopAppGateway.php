<?php

namespace Omnipay\Alipay;

use Omnipay\Alipay\Requests\AopTradeAppPayRequest;
/**
 * Class AopAppGateway
 * @package Omnipay\Alipay
 * @link    https://doc.open.alipay.com/docs/doc.htm?treeId=204&articleId=105051&docType=1
 */
class AopAppGateway extends \Omnipay\Alipay\AbstractAopGateway
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
     * @return AopTradeAppPayRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('Omnipay\\Alipay\\Requests\\AopTradeAppPayRequest', $parameters);
    }
}