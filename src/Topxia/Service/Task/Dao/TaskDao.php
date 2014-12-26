<?php

namespace Topxia\Service\Task\Dao;

interface TaskDao
{
    public function createTask($task);

    public function cancelTaskByClassName($taskClassName);

    public function getTask($id);

    public function findActiveTasks($time,$lock=false);

    public function updateTask($id,$fields);
}