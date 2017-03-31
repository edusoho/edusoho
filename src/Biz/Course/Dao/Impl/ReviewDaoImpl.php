<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\ReviewDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ReviewDaoImpl extends GeneralDaoImpl implements ReviewDao
{
    protected $table = 'course_review';

    public function getReviewByUserIdAndCourseId($userId, $courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND courseId = ? AND parentId = 0 LIMIT 1;";

        return $this->db()->fetchAssoc($sql, array($userId, $courseId)) ?: null;
    }

    public function sumRatingByParams($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(rating)');

        return $builder->execute()->fetchColumn(0);
    }

    public function deleteByCourseId($courseId)
    {
        return $this->db()->delete($this->table(), array('courseId' => $courseId));
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'meta' => 'json',
            ),
            'timestamps' => array(
                'createdTime', 'updatedTime',
            ),
            'orderbys' => array(
                'createdTime',
                'updatedTime',
                'rating',
            ),
            'conditions' => array(
                'userId = :userId',
                'rating = :rating',
                'content LIKE :content',
                'courseId = :courseId',
                'courseId IN (:courseIds)',
                'courseSetId IN (:courseSetIds)',
                'courseSetId = :courseSetId',
                'parentId = :parentId',
                'private = :private',
            ),
        );
    }
}
