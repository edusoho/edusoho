<?php

namespace Biz\Activity\Service;

interface ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data);

    public function findMyRecentLearnLogsByActivityIdAndEvent($activityId, $event);

    public function calcLearnProcessByCourseIdAndUserId($courseId, $userId);

    public function deleteLearnLogsByActivityId($activityId);

    public function getLastestLearnLogByActivityIdAndUserId($activityId, $userId);
}
