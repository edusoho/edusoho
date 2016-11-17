<?php


namespace Biz\Task\Service;


interface TaskResultService
{
    public function createTaskResult($taskResult);

    public function updateTaskResult($id, $taskResult);

    public function getTaskResultByTaskIdAndActivityId($taskId, $activityId);

    public function findTaskResultsByCourseId($courseId, $userId);

    public function getTaskResultByTaskIdAndUserId($courseTaskId, $userId);

    public function findUserProgressingTaskByCourseIdAndActivityId($courseId, $activityId);
}