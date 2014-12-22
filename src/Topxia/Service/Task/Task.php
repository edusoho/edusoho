<?php
namespace Topxia\Service\Task;

class Task
{   
    public function run()
    {
        //读数据库数据
        //执行计划

        $task=new "Topxia\\Service\\Task\\Activity".$taskClassName();

        $task->run();

    }

}

?>