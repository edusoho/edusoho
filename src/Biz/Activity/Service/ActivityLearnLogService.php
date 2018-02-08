<?php

namespace Biz\Activity\Service;

interface ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data);

    public function getMyRecentFinishLogByActivityId($activityId);

    public function calcLearnProcessByCourseIdAndUserId($courseId, $userId);

    public function deleteLearnLogsByActivityId($activityId);

    public function getLastestLearnLogByActivityIdAndUserId($activityId, $userId);

    // 统计时间段内 每个用户的学习时长
    public function sumLearnTimeGroupByUserId($conditions);

    public function search($conditions, $orderBy, $start, $limit);
}
