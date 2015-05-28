<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\ReviewDao;

class ReviewDaoImpl extends BaseDao implements ReviewDao
{
    protected $table = 'course_review';

    public function getReview($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findReviewsByCourseId($courseId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($courseId)) ? : array();
    }

    public function getReviewCountByCourseId($courseId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE courseId = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId));
    }

    public function addReview($review)
    {
        $affected = $this->getConnection()->insert($this->table, $review);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert review error.');
        }
        return $this->getReview($this->getConnection()->lastInsertId());
    }

    public function updateReview($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getReview($id);
    }

    public function getReviewByUserIdAndCourseId($userId, $courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND userId = ? LIMIT 1;";
        return $this->getConnection()->fetchAssoc($sql, array($courseId, $userId)) ? : null;
    }

    public function getReviewRatingSumByCourseId($courseId)
    {
        $sql = "SELECT sum(rating) FROM {$this->table} WHERE courseId = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId));
    }

    public function searchReviewsCount($conditions)
    {
         $builder = $this->createReviewSearchBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchReviews($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createReviewSearchBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array();
    }

    public function deleteReview($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    private function createReviewSearchBuilder($conditions)
    {
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
                ->andWhere('userId = :userId')
                ->andWhere('courseId = :courseId')
                ->andWhere('rating = :rating')
                ->andWhere('content LIKE :content')
                ->andWhere('courseId IN (:courseIds)')
                ->andWhere('private = :private');     

        return $builder;
    }

}