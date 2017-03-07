<?php

namespace Biz\Cash\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CashOrdersLogDao extends GeneralDaoInterface
{
    public function findByOrderId($orderId);
}
