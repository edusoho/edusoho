<?php

namespace Omnipay\Alipay;

use Omnipay\Alipay\Requests\LegacyExpressPurchaseRequest;
/**
 * Class LegacyExpressGateway
 * @package Omnipay\Alipay
 * @link    https://doc.open.alipay.com/docs/doc.htm?treeId=108&articleId=103950&docType=1
 */
class LegacyExpressGateway extends \Omnipay\Alipay\AbstractLegacyGateway
{
    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay Legacy Express Gateway';
    }
    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('Omnipay\\Alipay\\Requests\\LegacyExpressPurchaseRequest', $parameters);
    }
}