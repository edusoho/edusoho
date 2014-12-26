<?php
namespace Topxia\Service\Task;
use Topxia\Service\Common\ServiceKernel;

class Task
{   
    public function run()
    {
        ServiceKernel::instance()->createService('Task.TaskService')->run();
    }
}

?>