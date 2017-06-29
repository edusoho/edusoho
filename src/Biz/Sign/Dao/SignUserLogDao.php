<?php

namespace Biz\Sign\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SignUserLogDao extends GeneralDaoInterface
{
    public function findSignLogByPeriod($userId, $targetType, $targetId, $startTime, $endTime);
}
