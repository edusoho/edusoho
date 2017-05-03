<?php

namespace Biz\Activity\Service;

interface ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data);

    public function getMyRecentFinishLogByActivityId($activityId);

    public function calcLearnProcessByCourseIdAndUserId($courseId, $userId);

    public function deleteLearnLogsByActivityId($activityId);

    public function getLastestLearnLogByActivityIdAndUserId($activityId, $userId);
}
