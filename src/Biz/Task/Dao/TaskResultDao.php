<?php

namespace Biz\Task\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TaskResultDao extends GeneralDaoInterface
{
    public function findByCourseIdAndUserId($courseId, $userId);

    public function findByActivityIdAndUserId($activityId, $userId);

    public function getByTaskIdAndUserId($taskId, $userId);

    public function findByTaskIdsAndUserId($taskIds, $userId);

    public function deleteByTaskIdAndUserId($taskId, $userId);

    public function countLearnNumByTaskId($taskId);

    public function findFinishedTasksByCourseIdGroupByUserId($courseId);

    public function findFinishedTimeByCourseIdGroupByUserId($courseId);

    public function getLearnedTimeByCourseIdGroupByCourseTaskId($courseTaskIds);
}
