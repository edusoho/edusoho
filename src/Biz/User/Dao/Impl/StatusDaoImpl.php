<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\StatusDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class StatusDaoImpl extends AdvancedDaoImpl implements StatusDao
{
    protected $table = 'status';

    public function deleteByUserIdAndTypeAndObject($userId, $type, $objectType, $objectId)
    {
        return $this->db()->delete($this->table, [
            'userId' => $userId,
            'type' => $type,
            'objectType' => $objectType,
            'objectId' => $objectId,
        ]);
    }

    public function deleteByCourseId($courseId)
    {
        return $this->db()->delete($this->table(), ['courseId' => $courseId]);
    }

    public function findByCourseId($courseId)
    {
        return $this->findByFields(['courseId' => $courseId]);
    }

    public function declares()
    {
        return [
            'serializes' => [
                'properties' => 'json',
            ],
            'orderbys' => ['createdTime', 'courseId'],
            'conditions' => [
                'courseId = :courseId',
                'courseId IN ( :courseIds )',
                'courseId IN ( :classroomCourseIds ) OR classroomId = :classroomId',
                'classroomId = :onlyClassroomId',
                'objectType = :objectType',
                'objectId = :objectId',
                'userId = :userId',
                'userId IN ( :userIds )',
                'private = :private',
                'createdTime < :createdTime_LT',
            ],
        ];
    }
}
