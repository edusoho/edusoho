<?php

namespace Biz\Thread\Dao\Impl;

use Biz\Thread\Dao\ThreadPostDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadPostDaoImpl extends GeneralDaoImpl implements ThreadPostDao
{
    protected $table = 'thread_post';

    public function deletePostsByThreadId($threadId)
    {
        return $this->db()->delete($this->table, ['threadId' => $threadId]);
    }

    public function deletePostsByParentId($parentId)
    {
        return $this->db()->delete($this->table, ['parentId' => $parentId]);
    }

    public function deleteByUserId($userId)
    {
        return $this->db()->delete($this->table, ['userId' => $userId]);
    }

    public function findThreadIds($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('threadId');

        return $builder->execute()->fetchAll(0) ?: [];
    }

    public function declares()
    {
        $declares['orderbys'] = [
            'createdTime',
            'updatedTime',
            'ups',
        ];

        $declares['conditions'] = [
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
            'content LIKE :contentSearch',
            'auditStatus = :auditStatus',
            'auditStatus != :excludeAuditStatus',
        ];

        $declares['serializes'] = [
            'ats' => 'json',
        ];

        return $declares;
    }
}
