<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Resource\AbstractResource;

class OrderLog extends AbstractResource
{
    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function search(ApiRequest $request, $orderId)
    {
        $orderLogs = $this->getOrderService()->findOrderLogsByOrderId($orderId);

        $total = count($orderLogs);
        $offset = 0;
        $limit = $total;

        $this->getOCUtil()->replaceWithObjValue(
            $orderLogs,
            array('user_id' => array('nickname' => 'nickname', 'mobile' => 'verifiedMobile')),
            'user'
        );

        return $this->makePagingObject($orderLogs, $total, $offset, $limit);
    }

    protected function getOrderService()
    {
        return $this->service('Order:OrderService');
    }
}
