<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;

class Order extends Resource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        if (empty($params['targetId'])
            || empty($params['targetType'])) {
            throw new InvalidArgumentException();
        }

        list($order) = $this->service('Order:OrderFacadeService')
            ->createOrder($params['targetType'], $params['targetId'], $params);
        return $order;
    }
}