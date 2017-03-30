<?php

namespace ApiBundle\Api\Resource\OrderInfo;

use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class OrderInfo extends Resource
{
    public function add(Request $request)
    {
        $params = $request->request->all();

        if (empty($params['targetId']) || empty($params['targetType'])) {
            throw new InvalidArgumentException("缺少参数");
        }

        return $this->service('Order:OrderFacadeServic')->getOrderInfo($params['targetId'], $params['targetType'], array());
    }


}