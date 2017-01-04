<?php

namespace Biz\Activity\Service;

interface ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data);

    public function sumLearnedTimeByActivityId($activityId);

    public function findMyLearnLogsByActivityIdAndEvent($activityId, $event);

    public function calcLearnProcessByCourseIdAndUserId($courseId, $userId);
}
