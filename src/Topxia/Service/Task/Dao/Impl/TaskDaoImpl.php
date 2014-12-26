<?php

namespace Topxia\Service\Task\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Task\Dao\TaskDao;

class TaskDaoImpl extends BaseDao implements TaskDao 
{
    protected $table = 'task';
    private $serializeFields = array(
        'tagIds' => 'json',
    );

    public function createTask($task)
    {
        $affected = $this->getConnection()->insert($this->table, $task);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert task error.');
        }
        return $this->getTask($this->getConnection()->lastInsertId());
    }

    public function cancelTaskByClassName($taskClassName)
    {

    }

    public function getTask($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findActiveTasks($time,$lock=false)
    {
        $sql="SELECT * FROM {$this->table} WHERE startTime <= ? and status = ?  ". ($lock ? ' FOR UPDATE' : '');
        
        return $this->getConnection()->fetchAll($sql, array($time,'open'));
    }

    public function updateTask($id,$fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getTask($id);
    }
}