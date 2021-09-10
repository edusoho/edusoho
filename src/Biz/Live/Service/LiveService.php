<?php

namespace Biz\Live\Service;

interface LiveService
{
    public function confirmLiveStatus($liveIds);

    public function canExecuteLiveStatusJob($liveStatus, $jobType);

    public function createLiveRoom($params);

    public function updateLiveRoom($liveId, $params);

    public function deleteLiveRoom($liveId);

    public function canUpdateRoomType($liveStartTime);
}
