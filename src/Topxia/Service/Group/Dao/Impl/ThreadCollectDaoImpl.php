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

    public function getCollectThread($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getThreadByFromIdAndToId($userId, $threadId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND threadId = ?";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $threadId));
    }

    public function addCollect($collectThread)
    {
        $affected = $this->getConnection()->insert($this->table, $collectThread);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert collectThread error.');
        }
        return $this->getCollectThread($this->getConnection()->lastInsertId());
    }

    public function deleteCollect($userId,$threadId)
    {
       $sql = "DELETE FROM {$this->table} WHERE userId = ? AND threadId = ?";
        return $this->getConnection()->executeUpdate($sql, array($userId, $threadId));
    }

    public function findThreadCollectingCountByUserIdAndThreadId($userId,$threadId)
    {
        $sql = "SELECT COUNT(id)  FROM {$this->table} WHERE userId = ? AND threadId = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId, $threadId));
    }

    public function searchCollectThreadIdsCount($conditions)
    {
        $builder = $this->_createThreadSearchBuilder($conditions)
                         ->select('count(distinct threadId)');

        return $builder->execute()->fetchColumn(0); 
    }

    private function _createThreadSearchBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table,$this->table)
            ->andWhere('userId = :userId')
            ->andWhere('threadId = :threadId');
        return $builder;
    }

    public function searchCollectThreads($conditions,$orderBy,$start,$limit)
    {
        $builder=$this->_createThreadSearchBuilder($conditions)
        ->select('distinct threadId')
        ->setFirstResult($start)
        ->setMaxResults($limit)
        ->orderBy($orderBy[0],$orderBy[1]);

        return $builder->execute()->fetchAll() ? : array(); 
    }

}