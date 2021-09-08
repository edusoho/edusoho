<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface LiveActivityDao extends GeneralDaoInterface
{
    public function findByIds($Ids);

    public function findByLiveIdAndReplayStatus($liveId);

    public function findLiveActivitiesByReplayStatus($replayStatus = 'generated');

    public function getByLiveId($liveId);

    public function findByLiveIds($liveIds);

    public function getBySyncIdGTAndLiveId($liveId);

    public function getBySyncId($syncId);

    public function findLiveActivitiesByIsPublic();

    public function findLiveActivitiesByReplayTagId($tagId);
}
