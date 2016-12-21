<?php
namespace Biz\Classroom\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Classroom\Dao\ClassroomReviewDao;

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
            'rating'
        );

        $declares['conditions'] = array(
            'userId = :userId',
            'classroomId = :classroomId',
            'rating = :rating',
            'content LIKE :content',
            'parentId = :parentId',
            'classroomId IN (:classroomIds)'
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
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE classroomId = ? AND parentId = 0";
        return $this->db()->fetchColumn($sql, array($classroomId));
    }

    public function getByUserIdAndClassroomId($userId, $classroomId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? AND userId = ? AND parentId = 0 LIMIT 1;";
        return $this->db()->fetchAssoc($sql, array($classroomId, $userId)) ?: null;
    }


    protected function _createQueryBuilder($conditions)
    {
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }
        return parent::_createQueryBuilder($conditions);
    }
}
