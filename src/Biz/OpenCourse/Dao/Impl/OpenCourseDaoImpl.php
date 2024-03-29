<?php

namespace Biz\OpenCourse\Dao\Impl;

use Biz\OpenCourse\Dao\OpenCourseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class OpenCourseDaoImpl extends GeneralDaoImpl implements OpenCourseDao
{
    protected $table = 'open_course';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => ['teacherIds' => 'delimiter', 'tags' => 'delimiter'],
            'orderbys' => ['createdTime', 'recommendedSeq', 'studentNum', 'hitNum', 'seq', 'updatedTime'],
            'conditions' => [
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
                'id IN (:ids)',
                'recommended = :recommended',
                'locked = :locked',
                'orgCode PRE_LIKE :likeOrgCode',
                'orgCode = :orgCode',
            ],
        ];
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }
}
