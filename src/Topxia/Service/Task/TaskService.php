<?php
namespace Topxia\Service\Task;

interface TaskService
{
    public function createTask($type="single",$startTime,$taskClassName);

    public function cancelTaskByClassName($taskClassName);

    public function getTask($id);

    public function findActiveTasks($time,$lock=false);

    public function updateTask($id,$fields);

    public function run();
}

?>