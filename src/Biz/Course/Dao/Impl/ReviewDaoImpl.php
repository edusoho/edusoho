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
        $builder = $this->_createQueryBuilder($conditions)
            ->select('sum(rating)');

        return $builder->execute()->fetchColumn(0);
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'meta' => 'json'
            ),
            'orderbys'   => array(
                'createdTime',
                'updatedTime',
                'rating'
            ),
            'conditions' => array(
                'userId = :userId',
                'courseId = :courseId',
                'rating = :rating',
                'content LIKE :content',
                'courseId IN (:courseIds)',
                'courseSetId = :courseSetId',
                'parentId = :parentId',
                'private = :private'
            )
        );
    }
}
