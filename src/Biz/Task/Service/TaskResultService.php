<?php


namespace Biz\Task\Service;


interface TaskResultService
{
    public function createTaskResult($taskResult);

    public function updateTaskResult($id, $taskResult);

    public function deleteUserTaskResultByTaskId($taskId);

    public function waveLearnTime($id, $time);

    public function findUserTaskResultsByCourseId($courseId);

    public function countTaskResult($conditions);

    public function getUserTaskResultByTaskId($courseTaskId);

    public function findUserProgressingTaskResultByActivityId($activityId);

    public function findUserProgressingTaskResultByCourseId($courseId);

    public function getUserLatestFinishedTaskResultByCourseId($courseId);

    public function findUserTaskResultsByTaskIds($taskIds);
}