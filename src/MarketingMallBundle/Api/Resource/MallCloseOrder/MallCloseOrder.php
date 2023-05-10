<?php

namespace MarketingMallBundle\Api\Resource\MallCloseOrder;

use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Client\MarketingMallClient;

class MallCloseOrder extends BaseResource
{
    public function add(ApiRequest $request)
    {
        $client = new MarketingMallClient($this->getBiz());
        $params = [
            'sn' => $request->request->get('orderSn'),
        ];
        $client->closeOrder($params);

        return [
            'ok' => true,
        ];
    }
}
