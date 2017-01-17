<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseSetDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseSetDaoImpl extends GeneralDaoImpl implements CourseSetDao
{
    protected $table = 'c2_course_set';

    public function findCourseSetsByParentIdAndLocked($parentId, $locked)
    {
        return $this->getByFields(array('parentId' => $parentId, 'locked' => $locked));
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findLikeTitle($title)
    {
        $title = '%' . $title . '%';
        $sql = "SELECT * FROM {$this->table} WHERE title LIKE ?";

        return $this->db()->fetchAll($sql, array($title));
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'id IN ( :ids )',
                'status = :status',
                'categoryId = :categoryId',
                'title LIKE :title',
                'creator LIKE :creator',
                'type = :type',
                'recommended = :recommended'
            ),
            'serializes' => array(
                'tags'      => 'delimiter',
                'goals'     => 'delimiter',
                'audiences' => 'delimiter',
                'cover'     => 'json'
            ),
            'orderbys'   => array(
                'createdTime', 'recommendedSeq', 'hitNum', 'recommendedTime', 'rating', 'studentNum'
            ),
            'timestamps' => array(
                'createdTime', 'updatedTime'
            )
        );
    }
}
