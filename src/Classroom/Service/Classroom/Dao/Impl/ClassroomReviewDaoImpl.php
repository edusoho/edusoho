<?php
namespace Classroom\Service\Classroom\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Classroom\Service\Classroom\Dao\ClassroomReviewDao;

class ClassroomReviewDaoImpl extends BaseDao implements ClassroomReviewDao
{
    protected $table = 'classroom_review';

    public $serializeFields = array(
        'meta' => 'json'
    );

    public function getReview($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        $review = $this->getConnection()->fetchAssoc($sql, array($id));

        return $review ? $this->createSerializer()->unserialize($review, $this->serializeFields) : null;
    }

    public function getReviewRatingSumByClassroomId($classroomId)
    {
        $sql = "SELECT sum(rating) FROM {$this->table} WHERE classroomId = ? AND parentId = 0";

        return $this->getConnection()->fetchColumn($sql, array($classroomId));
    }

    public function getReviewCountByClassroomId($classroomId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE classroomId = ? AND parentId = 0";

        return $this->getConnection()->fetchColumn($sql, array($classroomId));
    }

    public function searchReviews($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $builder = $this->_createSearchBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addOrderBy($orderBy[0], $orderBy[1]);

        $reviews = $builder->execute()->fetchAll();
        return $reviews ? $this->createSerializer()->unserializes($reviews, $this->serializeFields) : array();
    }

    public function searchReviewCount($conditions)
    {
        $builder = $this->_createSearchBuilder($conditions)
            ->select('count(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function getReviewByUserIdAndClassroomId($userId, $classroomId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? AND userId = ? AND parentId = 0 LIMIT 1;";

        $review = $this->getConnection()->fetchAssoc($sql, array($classroomId, $userId)) ?: null;

        return $review ? $this->createSerializer()->unserialize($review, $this->serializeFields) : null;
    }

    public function addReview($review)
    {
        $review   = $this->createSerializer()->serialize($review, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $review);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert review error.');
        }

        return $this->getReview($this->getConnection()->lastInsertId());
    }

    public function updateReview($id, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getReview($id);
    }

    public function deleteReview($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    private function _createSearchBuilder($conditions)
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
