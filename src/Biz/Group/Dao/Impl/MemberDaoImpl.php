<?php

namespace Biz\Group\Dao\Impl;

use Biz\Group\Dao\MemberDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MemberDaoImpl extends GeneralDaoImpl implements MemberDao
{
    protected $table = 'groups_member';

    public function findByUserId($userId)
    {
        return $this->findByFields(['userId' => $userId]);
    }

    public function getByGroupIdAndUserId($groupId, $userId)
    {
        return $this->getByFields(['groupId' => $groupId, 'userId' => $userId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'serializes' => [],
            'orderbys' => ['postNum', 'name', 'createdTime'],
            'conditions' => [
                'groupId = :groupId',
                'role = :role',
                'userId = :userId',
                'courseId = :courseId',
                'courseId IN (:courseIds)',
            ],
        ];
    }
}
