<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\ReviewDao;

class ReviewDaoImpl extends BaseDao implements ReviewDao
{
    protected $table = 'course_review';

    public $serializeFields = array(
        'meta' => 'json'
    );

    public function getReview($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql    = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            $review = $that->getConnection()->fetchAssoc($sql, array($id));

            return $review ? $that->createSerializer()->unserialize($review, $that->serializeFields) : null;
        }

        );
    }

    public function findReviewsByCourseId($courseId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql     = "SELECT * FROM {$this->table} WHERE courseId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        $reviews = $this->getConnection()->fetchAll($sql, array($courseId)) ?: array();

        return $reviews ? $this->createSerializer()->unserializes($reviews, $this->serializeFields) : null;
    }

    public function getReviewCountByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:count", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT COUNT(id) FROM {$that->getTable()} WHERE courseId = ? AND parentId = 0";
            return $that->getConnection()->fetchColumn($sql, array($courseId));
        }

        );
    }

    public function addReview($review)
    {
        $review   = $this->createSerializer()->serialize($review, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $review);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert review error.');
        }

        return $this->getReview($this->getConnection()->lastInsertId());
    }

    public function updateReview($id, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getReview($id);
    }

    public function getReviewByUserIdAndCourseId($userId, $courseId)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:courseId:{$courseId}", $userId, $courseId, function ($userId, $courseId) use ($that) {
            $sql    = "SELECT * FROM {$that->getTable()} WHERE courseId = ? AND userId = ? AND parentId = 0 LIMIT 1;";
            $review = $that->getConnection()->fetchAssoc($sql, array($courseId, $userId));

            return $review ? $that->createSerializer()->unserialize($review, $that->serializeFields) : null;
        }

        );
    }

    public function getReviewRatingSumByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:sum:rating", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT sum(rating) FROM {$that->getTable()} WHERE courseId = ? AND parentId = 0";
            return $that->getConnection()->fetchColumn($sql, array($courseId));
        }

        );
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

        $reviews = $builder->execute()->fetchAll();
        return $reviews ? $this->createSerializer()->unserializes($reviews, $this->serializeFields) : array();
    }

    public function deleteReview($id)
    {
        $sql    = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
        $result = $this->getConnection()->executeUpdate($sql, array($id));
        $this->clearCached();
        return $result;
    }

    protected function createReviewSearchBuilder($conditions)
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
            ->andWhere('parentId = :parentId')
            ->andWhere('private = :private');

        return $builder;
    }
}
