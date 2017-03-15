<?php

namespace Biz\Classroom\Dao\Impl;

use Biz\Classroom\Dao\ClassroomReviewDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ClassroomReviewDaoImpl extends GeneralDaoImpl implements ClassroomReviewDao
{
    protected $table = 'classroom_review';

    public function declares()
    {
        $declares['serializes'] = array(
            'meta' => 'json',
        );

        $declares['orderbys'] = array(
            'createdTime',
            'updatedTime',
            'rating',
        );

        $declares['timestamps'] = array(
            'createdTime',
        );

        $declares['conditions'] = array(
            'userId = :userId',
            'classroomId = :classroomId',
            'rating = :rating',
            'content LIKE :content',
            'parentId = :parentId',
            'classroomId IN (:classroomIds)',
        );

        return $declares;
    }

    public function sumReviewRatingByClassroomId($classroomId)
    {
        $sql = "SELECT sum(rating) FROM {$this->table} WHERE classroomId = ? AND parentId = 0";

        return $this->db()->fetchColumn($sql, array($classroomId));
    }

    public function countReviewByClassroomId($classroomId)
    {
        return $this->count(array('classroomId' => $classroomId, 'parentId' => 0));
    }

    public function getByUserIdAndClassroomId($userId, $classroomId)
    {
        return $this->getByFields(array('userId' => $userId, 'classroomId' => $classroomId, 'parentId' => 0));
    }

    protected function createQueryBuilder($conditions)
    {
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        return parent::createQueryBuilder($conditions);
    }
}
