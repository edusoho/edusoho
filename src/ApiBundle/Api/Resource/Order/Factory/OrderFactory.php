<?php

namespace ApiBundle\Api\Resource\Order\Factory;

use Biz\Order\OrderException;

class OrderFactory extends BaseOrder
{
    public function create($action)
    {
        $orderAction = null;
        switch ($action) {
            case 'cancel':
                $orderAction = new OrderCancel();
                break;
            default:
                throw OrderException::UNKNOWN_ACTION();
        }

        return $orderAction;
    }
}
