<?php

namespace Codeages\Biz\Order\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface OrderItemDao extends AdvancedDaoInterface
{
    public function findByOrderId($orderId);

    public function findByOrderIds($orderIds);

    public function getOrderItemByOrderIdAndTargetIdAndTargetType($orderId, $targetId, $targetType);

    public function sumPayAmount($conditions);

    public function findByConditions($conditions);
}
