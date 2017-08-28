<?php

namespace Omnipay\Alipay;

use Omnipay\Alipay\Requests\AopTradePagePayRequest;

/**
 * Class AopPageGateway
 * @package Omnipay\Alipay
 * @link    https://doc.open.alipay.com/doc2/detail.htm?treeId=270&articleId=105901&docType=1
 */
class AopPageGateway extends AbstractAopGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay Page Gateway';
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
        return $this->createRequest(AopTradePagePayRequest::class, $parameters);
    }
}
