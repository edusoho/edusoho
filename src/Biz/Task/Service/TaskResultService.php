<?php

namespace Biz\Task\Service;

interface TaskResultService
{
    public function createTaskResult($taskResult);

    public function updateTaskResult($id, $taskResult);

    public function deleteUserTaskResultByTaskId($taskId);

    public function waveLearnTime($id, $time);

    public function findUserTaskResultsByCourseId($courseId);

    public function getUserTaskResultByTaskId($courseTaskId);

    public function findUserProgressingTaskResultByActivityId($activityId);

    public function findUserProgressingTaskResultByCourseId($courseId);

    public function getUserLatestFinishedTaskResultByCourseId($courseId);

    public function findUserTaskResultsByTaskIds($taskIds);

    public function countUsersByTaskIdAndLearnStatus($taskId, $status);

    /**
     * 统计某个任务的学习次数，学习的定义为task_result的status为start、finish，不对用户去重；
     */

    public function countTaskResults($conditions);

    public function searchTaskResults($conditions, $orderbys, $start, $limit);

    public function countLearnNumByTaskId($taskId);

    public function findFinishedTasksByCourseIdGroupByUserId($courseId);

    public function findFinishedTimeByCourseIdGroupByUserId($courseId);

    public function sumLearnTimeByCourseIdAndUserId($courseId, $userId);

    public function getLearnedTimeByCourseIdGroupByCourseTaskId($courseTaskId);

    public function getWatchTimeByCourseIdGroupByCourseTaskId($courseTaskId);
}
