<?php

namespace Omnipay\Alipay;

use Omnipay\Alipay\Requests\LegacyAppPurchaseRequest;
use Omnipay\Alipay\Requests\LegacyRefundNoPwdRequest;

/**
 * Class LegacyAppGateway
 * @package Omnipay\Alipay
 * @link    https://doc.open.alipay.com/doc2/detail?treeId=59&articleId=103563&docType=1
 */
class LegacyAppGateway extends AbstractLegacyGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay Legacy APP Gateway';
    }


    public function getDefaultParameters()
    {
        $data = parent::getDefaultParameters();

        $data['signType'] = 'RSA';

        return $data;
    }


    /**
     * @return mixed
     */
    public function getRnCheck()
    {
        return $this->getParameter('rn_check');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setRnCheck($value)
    {
        return $this->setParameter('rn_check', $value);
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(LegacyAppPurchaseRequest::class, $parameters);
    }


    /**
     * @param array $parameters
     *
     * @return LegacyRefundNoPwdRequest
     */
    public function refundNoPwd(array $parameters = [])
    {
        return $this->createRequest(LegacyRefundNoPwdRequest::class, $parameters);
    }
}
