<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\ReviewDao;

class ReviewDaoImpl extends BaseDao implements ReviewDao
{
    protected $table = 'course_review';


    public function getReview($id)
    {
        return $this->fetch($id);
    }

    public function findReviewsByCourseId($courseId, $start, $limit)
    {
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
        $id = $this->insert($review);
        return $this->getReview($id);
    }

    public function updateReview($id, $fields)
    {
        return $this->update($id, $fields);
    }

    public function getReviewByUserIdAndCourseId($userId, $courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND userId = ? LIMIT 1;";
        return $this->getConnection()->fetchAssoc($sql, array($courseId, $userId)) ? : null;
    }

    public function deleteReviewsByIds($ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }

    public function deleteReviewsByCourseId($courseId)
    {
        return $this->getConnection()->delete($this->table, array('courseId' => $courseId));
    }
    
    public function getReviewRatingSumByCourseId($courseId)
    {
        $sql = "SELECT sum(rating) FROM {$this->table} WHERE courseId = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId));
    }

    public function searchReviewsCount($conditions)
    {
         $builder = $this->createDynamicQueryBuilder($conditions)
                ->select('count(id)')
                ->from($this->table, 'course_review')
                ->andWhere('userId = :userId')
                ->andWhere('courseId = :courseId')
                ->andWhere('title LIKE :title')
                ->andWhere('content LIKE :content');
                return $builder->execute()->fetchColumn(0);
    }

    public function searchReviews($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
                ->select('*')
                ->from($this->table, 'course_review')
                ->andWhere('userId = :userId')
                ->andWhere('courseId = :courseId')
                ->andWhere('title LIKE :title')
                ->andWhere('content LIKE :content')
                ->orderBy("createdTime", "DESC")
                ->setFirstResult($start)
                ->setMaxResults($limit);
            return $builder->execute()->fetchAll() ? : array();
    }
}