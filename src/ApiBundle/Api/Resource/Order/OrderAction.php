<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Order\Factory\OrderFactory;

class OrderAction extends AbstractResource
{
    public function update(ApiRequest $request, $sn, $action)
    {
        $factory = new OrderFactory();
        $orderAction = $factory->create($action);
        $orderAction->setBiz($this->biz);

        return $orderAction->setOrderCanceled($sn);
    }
}
