<?php

namespace ApiBundle\Api\Resource\MarketingOrderInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Marketing\MarketingAPIFactory;
use Biz\Order\OrderException;

class MarketingOrderInfo extends AbstractResource
{
    public function get(ApiRequest $request, $id)
    {
        $client = MarketingAPIFactory::create();
        $systemUser = $this->getUserService()->getUserByType('system');

        $orderInfo = $client->get(
            '/order_infos/'.$id,
            array(),
            array('MERCHANT-USER-ID: '.$systemUser['id'])
        );

        if (isset($orderInfo['error'])) {
            throw OrderException::NOTFOUND_ORDER();
        }

        return $orderInfo;
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
