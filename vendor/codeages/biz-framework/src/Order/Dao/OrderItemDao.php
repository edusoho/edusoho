<?php

namespace Codeages\Biz\Framework\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderItemDao extends GeneralDaoInterface
{
    public function findByOrderId($orderId);
}