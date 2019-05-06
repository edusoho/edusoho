<?php

namespace  Biz\OpenCourse\Dao\Impl;

use Biz\OpenCourse\Dao\OpenCourseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class OpenCourseDaoImpl extends GeneralDaoImpl implements OpenCourseDao
{
    protected $table = 'open_course';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array('teacherIds' => 'delimiter', 'tags' => 'delimiter'),
            'orderbys' => array('createdTime', 'recommendedSeq', 'studentNum', 'hitNum', 'seq'),
            'conditions' => array(
                'updatedTime >= :updatedTime_GE',
                'status = :status',
                'type = :type',
                'title LIKE :titleLike',
                'userId = :userId',
                'userId IN ( :userIds )',
                'startTime >= :startTimeGreaterThan',
                'startTime < :startTimeLessThan',
                'rating > :ratingGreaterThan',
                'createdTime >= :startTime',
                'createdTime <= :endTime',
                'categoryId = :categoryId',
                'smallPicture = :smallPicture',
                'categoryId IN ( :categoryIds )',
                'parentId = :parentId',
                'parentId > :parentId_GT',
                'parentId IN ( :parentIds )',
                'id NOT IN ( :excludeIds )',
                'id IN ( :courseIds )',
                'id IN ( :openCourseIds )',
                'recommended = :recommended',
                'locked = :locked',
                'orgCode PRE_LIKE :likeOrgCode',
                'orgCode = :orgCode',
            ),
        );
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }
}
