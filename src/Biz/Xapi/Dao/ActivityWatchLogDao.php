<?php

namespace Biz\Xapi\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ActivityWatchLogDao extends GeneralDaoInterface
{
    public function getLatestWatchLogByUserIdAndActivityId($userId, $activityId, $isPush = 0);

    public function findByIds($ids);
}
