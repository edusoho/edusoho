<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface LiveActivityDao extends GeneralDaoInterface
{
    public function findByIds($Ids);

    public function findByLiveIdAndReplayStatus($liveId);
}
