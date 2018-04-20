<?php

namespace Biz\Activity\Service;

interface LiveActivityService
{
    public function getLiveActivity($id);

    public function findLiveActivitiesByIds($ids);

    public function createLiveActivity($activity, $ignoreValidation = false);

    public function updateLiveActivity($id, &$fields, $activity);

    public function updateLiveStatus($liveId, $status);

    public function deleteLiveActivity($id);

    public function createLiveroom($activity);

    public function search($conditions, $orderbys, $start, $limit);

    public function canUpdateRoomType($liveStartTime);
}
