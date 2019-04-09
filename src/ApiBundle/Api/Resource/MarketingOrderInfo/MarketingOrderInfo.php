<?php

namespace ApiBundle\Api\Resource\MarketingOrderInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Marketing\MarketingAPIFactory;

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
            throw new NotFoundHttpException('订单不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        return $orderInfo;
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
