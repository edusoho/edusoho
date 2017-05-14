<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\StatusDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class StatusDaoImpl extends GeneralDaoImpl implements StatusDao
{
    protected $table = 'status';

    public function deleteByUserIdAndTypeAndObject($userId, $type, $objectType, $objectId)
    {
        return $this->db()->delete($this->table, array(
            'userId' => $userId,
            'type' => $type,
            'objectType' => $objectType,
            'objectId' => $objectId,
        ));
    }

    public function deleteByCourseIdAndTypeAndObject($courseId, $type, $objectType, $objectId)
    {
        return $this->db()->delete($this->table, array(
            'courseId' => $courseId,
            'type' => $type,
            'objectType' => $objectType,
            'objectId' => $objectId,
        ));
    }

    public function deleteByCourseId($courseId)
    {
        return $this->db()->delete($this->table(), array('courseId' => $courseId));
    }

    public function findByCourseId($courseId)
    {
        return $this->findByFields(array('courseId' => $courseId));
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'properties' => 'json',
            ),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'courseId = :courseId',
                'courseId IN ( :courseIds )',
                'courseId IN ( :classroomCourseIds ) OR classroomId = :classroomId',
                'classroomId = :onlyClassroomId',
                'objectType = :objectType',
                'objectId = :objectId',
                'userId = :userId',
                'userId IN ( :userIds )',
                'private = :private',
            ),
        );
    }
}
