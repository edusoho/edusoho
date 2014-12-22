<?php
namespace Topxia\Service\Task\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Task\TaskService;

class TaskServiceImpl extends BaseService implements TaskService
{   
    
    public function createTask($type="single",$startTime,$taskClassName)
    {
        $task=array(
            'type'=>$type,
            'startTime'=>$startTime,
            'taskName'=>$taskClassName);

        $task=$this->getTaskDao()->createTask($task);

        return $task;
    }

    public function cancelTaskByClassName($taskClassName)
    {

    }

    public function getTask($id)
    {
        return $this->getTaskDao()->getTask($id);
    }

    public function findActiveTasks($time,$lock=false)
    {
        return $this->getTaskDao()->findActiveTasks($time,$lock);
    }

    public function run()
    {   
        try {

            $this->getTaskDao()->getConnection()->beginTransaction();
            $tasks=$this->findActiveTasks(time(),true);

            if($tasks){

                foreach ($tasks as $task) {
                   
                   $this->init($task['taskName']);

                   $this->updateTask($task['id'],array('status'=>'close'));
                }
            }

            $this->getTaskDao()->getConnection()->commit();

        }catch (\Exception $e) {

            $this->getTaskDao()->getConnection()->rollback();

            throw $e;
        }
    }

    private function init($taskClassName)
    {
        $class="Topxia\\Service\\Task\\Activity\\".$taskClassName;

        $task=new $class;

        $task->run();  
    }

    public function updateTask($id,$fields)
    {
        return $this->getTaskDao()->updateTask($id,$fields);
    }

    protected function getTaskDao()
    {
        return $this->createDao('Task.TaskDao');
    }   

}