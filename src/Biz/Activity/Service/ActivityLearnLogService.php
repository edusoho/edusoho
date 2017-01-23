<?php

namespace Biz\Activity\Service;

interface ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data);

//得到当前用户观看时间
    public function sumMyLearnedTimeByActivityId($activityId);

//得到活动的总观看时间
    public function sumLearnedTimeByActivityId($activityId);

    public function findMyLearnLogsByActivityIdAndEvent($activityId, $event);

    public function calcLearnProcessByCourseIdAndUserId($courseId, $userId);

    public function sumLearnTime($conditions);

    public function sumWatchTime($conditions);

    public function deleteLearnLogsByActivityId($activityId);
}
