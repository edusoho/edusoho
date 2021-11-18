<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface LiveActivityDao extends AdvancedDaoInterface
{
    public function findByIds($Ids);

    public function findByLiveIdAndReplayStatus($liveId);

    public function getByLiveId($liveId);

    public function findByLiveIds($liveIds);

    public function getBySyncIdGTAndLiveId($liveId);

    public function getBySyncId($syncId);

    public function findLiveActivitiesByReplayTagId($tagId);
}
