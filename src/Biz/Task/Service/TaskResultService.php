<?php

namespace Biz\Task\Service;

interface TaskResultService
{
    public function analysisCompletedTaskDataByTime($startTime, $endTime);

    public function getTaskResult($resultId);

    public function createTaskResult($taskResult);

    public function updateTaskResult($id, $taskResult);

    public function deleteUserTaskResultByTaskId($taskId);

    public function deleteTaskResultsByTaskId($taskId);

    public function checkUserWatchNum($taskId);

    public function waveLearnTime($id, $time);

    public function waveWatchTime($id, $watchTime);

    public function findUserTaskResultsByCourseId($courseId);

    public function getUserTaskResultByTaskId($taskId);

    public function findUserProgressingTaskResultByActivityId($activityId);

    public function findUserProgressingTaskResultByCourseId($courseId);

    public function findUserFinishedTaskResultsByCourseId($courseId);

    public function getUserLatestFinishedTaskResultByCourseId($courseId);

    public function findUserTaskResultsByTaskIds($taskIds);

    public function countUsersByTaskIdAndLearnStatus($taskId, $status);

    /**
     * 统计某个任务的学习次数，学习的定义为task_result的status为start、finish，不对用户去重；.
     */
    public function countTaskResults($conditions);

    public function searchTaskResults($conditions, $orderbys, $start, $limit);

    public function countLearnNumByTaskId($taskId);

    public function findFinishedTimeByCourseIdGroupByUserId($courseId);

    public function sumLearnTimeByCourseIdAndUserId($courseId, $userId);

    public function getLearnedTimeByCourseIdGroupByCourseTaskId($courseTaskId);

    public function getWatchTimeByCourseIdGroupByCourseTaskId($courseTaskId);

    public function getWatchTimeByActivityIdAndUserId($activityId, $userId);

    public function getMyLearnedTimeByActivityId($activityId);

    public function countFinishedTasksByUserIdAndCourseIdsGroupByCourseId($userId, $courseIds);

    public function countFinishedCompulsoryTasksByUserIdAndCourseId($userId, $courseId);

    public function findTaskresultsByTaskId($taskId);

    public function countTaskNumGroupByUserId($conditions);

    public function getTaskResultByTaskIdAndUserId($taskId, $userId);
}
