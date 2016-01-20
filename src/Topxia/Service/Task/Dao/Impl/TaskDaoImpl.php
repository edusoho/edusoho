<?php

namespace Topxia\Service\Task\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Task\Dao\TaskDao;

class TaskDaoImpl extends BaseDao implements TaskDao
{
    protected $table         = 'task';
    private $serializeFields = array(
        'meta' => 'json'
    );

    public function getTask($id)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $task = $this->getConnection()->fetchAssoc($sql, array($id));

        return $task ? $this->createSerializer()->unserialize($task, $this->serializeFields) : null;
    }

    public function findUserTasksByBatchIdAndTaskType($userId, $batchId, $taskType)
    {
        $sql   = "SELECT * FROM {$this->table} WHERE `userId`=? AND `taskType`=? AND `batchId`=?";
        $tasks = $this->getConnection()->fetchAll($sql, array($userId, $taskType, $batchId));
        return $tasks ? $this->createSerializer()->unserializes($tasks, $this->serializeFields) : null;
    }

    public function findUserCompletedTasks($userId, $batchId)
    {
        $sql   = "SELECT * FROM {$this->table} WHERE `userId`=? AND `batchId`=? AND `status`='completed' ORDER BY `completedTime` ASC";
        $tasks = $this->getConnection()->fetchAll($sql, array($userId, $batchId));
        return $tasks ? $this->createSerializer()->unserializes($tasks, $this->serializeFields) : null;
    }

    public function addTask(array $fields)
    {
        $fields   = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert task error.');
        }

        return $this->getTask($this->getConnection()->lastInsertId());
    }

    public function updateTask($id, array $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getTask($id);
    }

    public function deleteTask($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteTasksByBatchIdAndTaskTypeAndUserId($batchId, $taskType, $userId)
    {
        return $this->getConnection()->delete($this->table, array('batchId' => $batchId, 'taskType' => $taskType, 'userId' => $userId));
    }

    public function searchTasks($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $orderBy = $this->checkOrderBy($orderBy, array('id', 'createdTime', 'taskStartTime', 'taskEndTime', 'completedTime', 'intervalDate'));

        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('*')
                        ->orderBy($orderBy[0], $orderBy[1])
                        ->setFirstResult($start)
                        ->setMaxResults($limit);

        $tasks = $builder->execute()->fetchAll();
        return $tasks ? $this->createSerializer()->unserializes($tasks, $this->serializeFields) : array();
    }

    public function searchTaskCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, $this->table)
                        ->andWhere('status = :status')
                        ->andWhere('batchId = :batchId')
                        ->andWhere('taskType = :taskType')
                        ->andWhere('targetId = :targetId')
                        ->andWhere('targetType = :targetType')
                        ->andWhere('title LIKE :titleLike')
                        ->andWhere('userId = :userId')
                        ->andWhere('taskStartTime > :taskStartTimeGreaterThan')
                        ->andWhere('taskStartTime < :taskStartTimeLessThan')
                        ->andWhere('taskEndTime >= :taskEndTimeGreaterThan')
                        ->andWhere('taskEndTime <= :taskEndTimeLessThan')
                        ->andWhere('intervalDate > intervalDateGreaterThan')
                        ->andWhere('batchId IN ( :batchIds )');

        return $builder;
    }
}
