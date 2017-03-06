<?php

namespace Biz\Sign\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SignTargetStatisticsDao extends GeneralDaoInterface
{
    public function getByTargetTypeAndTargetIdAndDate($targetType, $targetId, $date);
}
