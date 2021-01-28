<?php

namespace Codeages\Biz\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderItemDeductDao extends GeneralDaoInterface
{
    public function findByItemId($itemId);

    public function findByOrderId($orderId);
}