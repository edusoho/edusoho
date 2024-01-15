<?php

namespace Biz\Activity\Service;

interface ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data);

    public function getMyRecentFinishLogByActivityId($activityId);

    /**
     * @deprecated
     */
    public function calcLearnProcessByCourseIdAndUserId($courseId, $userId);

    public function deleteLearnLogsByActivityId($activityId);
}
