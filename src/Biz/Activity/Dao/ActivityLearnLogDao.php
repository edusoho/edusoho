<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ActivityLearnLogDao extends GeneralDaoInterface
{
    public function sumLearnedTimeByActivityId($activityId);

    public function sumLearnedTimeByActivityIdAndUserId($activityId, $userId);

    public function sumLearnedTimeByCourseIdAndUserId($courseId, $userId);

    public function findByActivityIdAndUserIdAndEvent($activityId, $userId, $event);

    public function countLearnedDaysByCourseIdAndUserId($courseId, $userId);

    public function sumLearnTime($conditions);
}
