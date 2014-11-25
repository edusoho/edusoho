<?php

namespace Topxia\Service\Group\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Group\Dao\ThreadCollectDao;

class ThreadCollectDaoImpl extends BaseDao implements ThreadCollectDao 
{
    protected $table = 'groups_thread_collect';
    private $serializeFields = array(
        'tagIds' => 'json',
    );

    public function getThreadCollect($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getThreadByUserIdAndThreadId($userId, $threadId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND threadId = ?";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $threadId));
    }

    public function addThreadCollect($collectThread)
    {
        $affected = $this->getConnection()->insert($this->table, $collectThread);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert threadCollect error.');
        }
        return $this->getThreadCollect($this->getConnection()->lastInsertId());
    }

    public function deleteThreadCollectByUserIdAndThreadId($userId,$threadId)
    {
       $sql = "DELETE FROM {$this->table} WHERE userId = ? AND threadId = ?";
        return $this->getConnection()->executeUpdate($sql, array($userId, $threadId));
    }

    public function findThreadCollectingCountByUserIdAndThreadId($userId,$threadId)
    {
        $sql = "SELECT COUNT(id)  FROM {$this->table} WHERE userId = ? AND threadId = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId, $threadId));
    }

    public function searchThreadCollectCount($conditions)
    {
        $builder = $this->_createThreadCollectSearchBuilder($conditions)
                         ->select('count(distinct threadId)');

        return $builder->execute()->fetchColumn(0); 
    }

    private function _createThreadCollectSearchBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table,$this->table)
            ->andWhere('userId = :userId')
            ->andWhere('threadId = :threadId');
        return $builder;
    }

    public function searchThreadCollects($conditions,$orderBy,$start,$limit)
    {
        $builder=$this->_createThreadCollectSearchBuilder($conditions)
        ->select('distinct threadId')
        ->setFirstResult($start)
        ->setMaxResults($limit)
        ->orderBy($orderBy[0],$orderBy[1]);

        return $builder->execute()->fetchAll() ? : array(); 
    }

}