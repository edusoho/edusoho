<?php

namespace Biz\RewardPoint\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AccountFlowDao extends GeneralDaoInterface
{
    public function getInflowByUserIdAndTarget($userId, $targetId, $targetType);

    public function sumAccountOutFlowByUserId($userId);

    public function sumInflowByUserIdAndWayAndTime($userId, $way, $startTime, $endTime);

    public function sumInflowByUserId($userId);
}
