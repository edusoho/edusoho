<?php

namespace Biz\Sign\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface SignUserStatisticsDao extends AdvancedDaoInterface
{
    public function getStatisticsByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);
}
