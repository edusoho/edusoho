<?php

namespace Biz\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderRefundDao extends GeneralDaoInterface
{
    public function countByUserId($userId);

    public function findByUserId($userId, $start, $limit);

    public function findByIds(array $ids);

    public function getByOrderId($orderId);

    public function findByOrderIds(array $orderIds);
}
