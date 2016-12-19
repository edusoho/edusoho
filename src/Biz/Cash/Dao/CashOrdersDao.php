<?php

namespace Biz\Cash\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CashOrdersDao extends GeneralDaoInterface
{
    public function getBySn($sn, $lock = false);

    public function getByToken($token);

    public function analysisAmount($conditions);

    public function closeOrders($time);
}
