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
            'updatedTime'
        );

        return $declares;
    }

    public function sumReviewRatingByClassroomId($classroomId)
    {
        $sql = "SELECT sum(rating) FROM {$this->table} WHERE classroomId = ? AND parentId = 0";
        return $this->getConnection()->fetchColumn($sql, array($classroomId));
    }

    public function countReviewByClassroomId($classroomId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE classroomId = ? AND parentId = 0";
        return $this->getConnection()->fetchColumn($sql, array($classroomId));
    }

    public function getByUserIdAndClassroomId($userId, $classroomId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? AND userId = ? AND parentId = 0 LIMIT 1;";
        $review = $this->getConnection()->fetchAssoc($sql, array($classroomId, $userId)) ?: null;
        return $review ? $this->createSerializer()->unserialize($review, $this->serializeFields) : null;
    }


    private function _createQueryBuilder($conditions)
    {
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('userId = :userId')
            ->andWhere('classroomId = :classroomId')
            ->andWhere('rating = :rating')
            ->andWhere('content LIKE :content')
            ->andWhere('parentId = :parentId')
            ->andWhere('classroomId IN (:classroomIds)');

        return $builder;
    }
}
