<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseSetDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseSetDaoImpl extends GeneralDaoImpl implements CourseSetDao
{
    protected $table = 'c2_course_set';

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
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
                'type = :type'
            ),
            'serializes' => array(
                'tags'      => 'delimiter',
                'goals'     => 'delimiter',
                'audiences' => 'delimiter',
                'cover'     => 'json'
            ),
            'orderbys' => array(
                'createdTime'
            ),
            'timestamps' => array(
                'createdTime', 'updatedTime'
            )
        );
    }
}
