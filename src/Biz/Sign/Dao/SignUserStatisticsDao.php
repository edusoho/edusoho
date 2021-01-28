<?php

namespace Biz\Sign\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SignUserStatisticsDao extends GeneralDaoInterface
{
    public function getStatisticsByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);
}
