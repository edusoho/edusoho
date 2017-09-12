<?php

namespace Omnipay\Alipay;

use Omnipay\Alipay\Requests\AopTradeWapPayRequest;

/**
 * Class AopWapGateway
 * @package Omnipay\Alipay
 * @link    https://doc.open.alipay.com/docs/doc.htm?treeId=203&articleId=105288&docType=1
 */
class AopWapGateway extends AbstractAopGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay WAP Gateway';
    }


    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->getParameter('return_url');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setReturnUrl($value)
    {
        return $this->setParameter('return_url', $value);
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(AopTradeWapPayRequest::class, $parameters);
    }
}
