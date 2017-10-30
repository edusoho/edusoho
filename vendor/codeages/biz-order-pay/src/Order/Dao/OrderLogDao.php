<?php

namespace Codeages\Biz\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderLogDao extends GeneralDaoInterface
{
    public function findOrderLogsByOrderId($orderId);
}