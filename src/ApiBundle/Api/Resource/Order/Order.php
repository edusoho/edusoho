<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ExceptionCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Order extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        if (empty($params['targetId'])
            || empty($params['targetType'])) {
            throw new BadRequestHttpException('Params missing', null, ExceptionCode::INVALID_ARGUMENT);
        }

        list($order) = $this->service('Order:OrderFacadeService')
            ->createOrder($params['targetType'], $params['targetId'], $params);
        return $order;
    }
}