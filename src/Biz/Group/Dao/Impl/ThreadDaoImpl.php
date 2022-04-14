<?php

namespace Biz\Group\Dao\Impl;

use Biz\Group\Dao\ThreadDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadDaoImpl extends GeneralDaoImpl implements ThreadDao
{
    protected $table = 'groups_thread';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByGroupId($groupId)
    {
        return $this->findByFields(['groupId' => $groupId]);
    }

    public function deleteByGroupId($groupId)
    {
        return $this->db()->delete($this->table(), ['groupId' => $groupId]);
    }

    public function deleteByUserId($userId)
    {
        return $this->db()->delete($this->table(), ['userId' => $userId]);
    }

    public function createQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['title'] = '%'.$conditions['title'].'%';
        }

        return parent::createQueryBuilder($conditions);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => ['tagIds' => 'json'],
            'orderbys' => ['isStick', 'postNum', 'createdTime', 'lastPostTime', 'updatedTime'],
            'conditions' => [
                'groupId = :groupId',
                'createdTime > :createdTime',
                'updatedTime >= :updatedTime_GE',
                'isElite = :isElite',
                'isStick = :isStick',
                'type = :type',
                'userId = :userId',
                'status = :status',
                'title like :title',
                'auditStatus = :auditStatus',
                'auditStatus != :excludeAuditStatus',
            ],
        ];
    }
}
