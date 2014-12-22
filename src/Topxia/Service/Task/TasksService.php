<?php
namespace Topxia\Service\Task;

interface TasksService
{
    public function createTask($cornType,$cornStartTime,$cornEndedTime,$taskClassName);

    public function cancelTaskByClassName($taskClassName);

    public function getTask($id);

    public function findActiveTasks($time);
}

?>