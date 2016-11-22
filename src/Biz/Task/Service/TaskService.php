<?php

namespace Biz\Task\Service;

interface TaskService
{
    public function getTask($id);

    public function createTask($task);

    public function updateTask($id, $fields);

    public function deleteTask($id);

    public function findTasksByCourseId($courseId);

    public function findTasksWithLearningResultByCourseId($courseId);

    public function startTask($taskId);

    public function finishTask($taskId);

    public function tryTakeTask($taskId);

    /**
     * return next Task that can be learned of the  course plan
     * @param $taskId
     * @return mixed
     *
     */
    public function getNextTask($taskId);

    /**
     *  return if the task can learn or not
     * @param $taskId
     * @return  True|False
     */
    public function canLearnTask($taskId); // 任务是否可学

    /**
     * return if the task has been learned
     * @param $taskId
     * @return True|False
     */
    public function isTaskLearned($taskId);
}

