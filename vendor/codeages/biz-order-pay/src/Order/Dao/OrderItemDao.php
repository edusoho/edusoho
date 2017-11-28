<?php

namespace Codeages\Biz\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderItemDao extends GeneralDaoInterface
{
    public function findByOrderId($orderId);

    public function findByOrderIds($orderIds);

    public function getOrderItemByOrderIdAndTargetIdAndTargetType($orderId, $targetId, $targetType);

    public function sumPayAmount($conditions);

    public function findByConditions($conditions);
}
