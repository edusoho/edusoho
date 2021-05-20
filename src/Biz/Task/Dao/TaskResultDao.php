<?php

namespace Biz\Task\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface TaskResultDao extends AdvancedDaoInterface
{
    public function analysisCompletedTaskDataByTime($startTime, $endTime);

    public function findByCourseIdAndUserId($courseId, $userId);

    public function findByActivityIdAndUserId($activityId, $userId);

    public function getByActivityIdAndUserId($activityId, $userId);

    public function getByTaskIdAndUserId($taskId, $userId);

    public function findByTaskIdsAndUserId($taskIds, $userId);

    public function deleteByTaskIdAndUserId($taskId, $userId);

    public function deleteByTaskId($taskId);

    public function deleteByCourseId($courseId);

    public function countLearnNumByTaskId($taskId);

    public function findFinishedTimeByCourseIdGroupByUserId($courseId);

    public function sumLearnTimeByCourseIdAndUserId($courseId, $userId);

    public function getLearnedTimeByCourseIdGroupByCourseTaskId($courseTaskIds);

    public function getWatchTimeByCourseIdGroupByCourseTaskId($courseTaskId);

    public function countFinishedTasksByUserIdAndCourseIdsGroupByCourseId($userId, $courseIds);

    public function countFinishedCompulsoryTasksByUserIdAndCourseId($userId, $courseId);

    public function countFinishedCompulsoryTasksByUserIdAndCourseIds($userId, array $courseIds);

    public function findTaskresultsByTaskId($taskId);

    public function findByUserId($userId);

    public function sumCourseSetLearnedTimeByTaskIds($taskIds);

    public function countTaskNumGroupByUserId($conditions);

    public function countUserNumByCourseTaskId($conditions);

    public function countFinishedCompulsoryTaskNumGroupByUserId($courseId);
}
