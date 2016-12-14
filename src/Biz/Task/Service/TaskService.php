<?php

namespace Biz\Task\Service;

interface TaskService
{
    const LEARN_TIME_STEP = 2;

    public function getTask($id);

    public function createTask($task);

    public function updateTask($id, $fields);

    public function publishTask($id);

    public function unpublishTask($id);

    public function deleteTask($id);

    public function findTasksByCourseId($courseId);

    public function countTasksByCourseId($courseId);

    public function findTasksFetchActivityByCourseId($courseId);

    public function findUserTasksFetchActivityAndResultByCourseId($courseId);

    public function startTask($taskId);

    public function doTask($taskId, $time = TaskService::LEARN_TIME_STEP);

    public function finishTask($taskId);

    public function isFinished($taskId);

    public function tryTakeTask($taskId);

    /**
     * return next Task that can be learned of the  course plan, or return empty array()
     * @param  $taskId
     * @return mixed
     */
    public function getNextTask($taskId);

    /**
     *  return if the task can learn or not
     * @param  $taskId
     * @return True|False
     */
    public function canLearnTask($taskId); // 任务是否可学

    /**
     * return if the task has been learned
     * @param  $taskId
     * @return True|False
     */
    public function isTaskLearned($taskId);

    public function getMaxSeqByCourseId($courseId);

    public function findTasksByChapterId($chapterId);

    public function findTasksFetchActivityByChapterId($chapterId);

    public function finishTaskResult($taskId);

}
