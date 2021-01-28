<?php

namespace Biz\Thread\Dao\Impl;

use Biz\Thread\Dao\ThreadPostDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadPostDaoImpl extends GeneralDaoImpl implements ThreadPostDao
{
    protected $table = 'thread_post';

    public function deletePostsByThreadId($threadId)
    {
        return $this->db()->delete($this->table, array('threadId' => $threadId));
    }

    public function deletePostsByParentId($parentId)
    {
        return $this->db()->delete($this->table, array('parentId' => $parentId));
    }

    public function findThreadIds($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('threadId');

        return $builder->execute()->fetchAll(0) ?: array();
    }

    public function declares()
    {
        $declares['orderbys'] = array(
            'createdTime',
            'updatedTime',
            'ups',
        );

        $declares['conditions'] = array(
            'userId = :userId',
            'userId NOT IN (:notUserIds)',
            'userId IN (:userIds)',
            'id < :id',
            'id < :lessThanId',
            'id >= :greaterThanId',
            'ups >= :ups_GT',
            'id NOT IN (:excludeIds)',
            'createdTime >= :GTEcreatedTime',
            'parentId = :parentId',
            'threadId = :threadId',
            'targetId = :targetId',
            'targetId IN (:targetIds)',
            'targetType = :targetType',
            'adopted = :adopted',
        );

        $declares['serializes'] = array(
            'ats' => 'json',
        );

        return $declares;
    }
}
