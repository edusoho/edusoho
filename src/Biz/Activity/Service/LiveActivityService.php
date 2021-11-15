<?php

namespace Biz\Activity\Service;

use Biz\System\Annotation\Log;

interface LiveActivityService
{
    public function getLiveActivity($id);

    public function findLiveActivitiesByIds($ids);

    public function findLiveActivitiesByReplayStatus($replayStatus = 'generated');

    public function createLiveActivity($activity, $ignoreValidation = false);

    /**
     * @param $id
     * @param $fields
     * @param $activity
     *
     * @return mixed
     * @Log(module="live",action="update_live_activity",funcName="findActivityByLiveActivityId",param="id")
     */
    public function updateLiveActivity($id, $fields, $activity);

    public function updateLiveStatus($liveId, $status);

    public function startLive($liveId, $startTime);

    public function closeLive($liveId, $closeTime);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="live",action="delete_live_activity",funcName="findActivityByLiveActivityId")
     */
    public function deleteLiveActivity($id);

    public function createLiveroom($activity);

    public function search($conditions, $orderbys, $start, $limit);

    public function count($conditions);

    public function updateLiveActivityWithoutEvent($liveActivityId, $fields);

    public function shareLiveReplay($liveActivityId);

    public function unShareLiveReplay($liveActivityId);

    public function updateLiveReplayTags($liveActivityId, $tagIds);

    public function removeLiveReplay($liveActivityId);

    public function canUpdateRoomType($liveStartTime);

    public function getByLiveId($liveId);

    public function findLiveActivitiesByLiveIds($liveIds);

    public function getBySyncIdGTAndLiveId($liveId);

    public function findLiveActivitiesByIsPublic();

    public function findLiveActivitiesByReplayTagId($tagId);
}
