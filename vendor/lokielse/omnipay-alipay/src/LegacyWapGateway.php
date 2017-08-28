<?php

namespace Omnipay\Alipay;

use Omnipay\Alipay\Requests\LegacyWapPurchaseRequest;

/**
 * Class LegacyWapGateway
 * @package  Omnipay\Alipay
 * @link     https://doc.open.alipay.com/docs/doc.htm?treeId=60&articleId=103564&docType=1
 */
class LegacyWapGateway extends AbstractLegacyGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay Legacy Wap Gateway';
    }


    /**
     * @param array $parameters
     *
     * @return LegacyWapPurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(LegacyWapPurchaseRequest::class, $parameters);
    }
}
