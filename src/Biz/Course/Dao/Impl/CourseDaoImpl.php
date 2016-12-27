<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseDaoImpl extends GeneralDaoImpl implements CourseDao
{
    protected $table = 'c2_course';

    public function findCoursesByCourseSetId($courseSetId)
    {
        return $this->findInField('courseSetId', array($courseSetId));
    }

    public function getDefaultCourseByCourseSetId($courseSetId)
    {
        return $this->getByFields(array('courseSetId' => $courseSetId, 'isDefault' => 1));
    }

    public function getFirstPublishedByCourseSetId($courseSetId)
    {
        $records = $this->search(array('courseSetId' => $courseSetId, 'status' => 'published'), array('createdTime' => 'ASC'), 0, 1);
        if (!empty($records)) {
            return $records[0];
        }
        return null;
    }

    public function findCoursesByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'goals'      => 'delimiter',
                'audiences'  => 'delimiter',
                'services'   => 'delimiter',
                'teacherIds' => 'delimiter'
            ),
            'orderbys'   => array('hitNum', 'recommendedTime', 'rating', 'studentNum', 'recommendedSeq', 'createdTime'),
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'courseSetId = :courseSetId',
                'updatedTime >= :updatedTime_GE',
                'status = :status',
                'type = :type',
                'price = :price',
                'price > :price_GT',
                'originPrice > :originPrice_GT',
                'originPrice = :originPrice',
                'coinPrice > :coinPrice_GT',
                'coinPrice = :coinPrice',
                'originCoinPrice > :originCoinPrice_GT',
                'originCoinPrice = :originCoinPrice',
                'title LIKE :titleLike',
                'userId = :userId',
                'recommended = :recommended',
                'startTime >= :startTimeGreaterThan',
                'startTime < :startTimeLessThan',
                'rating > :ratingGreaterThan',
                'vipLevelId >= :vipLevelIdGreaterThan',
                'vipLevelId = :vipLevelId',
                'createdTime >= :startTime',
                'createdTime <= :endTime',
                'categoryId = :categoryId',
                'smallPicture = :smallPicture',
                'categoryId IN ( :categoryIds )',
                'vipLevelId IN ( :vipLevelIds )',
                'parentId = :parentId',
                'parentId > :parentId_GT',
                'parentId IN ( :parentIds )',
                'id NOT IN ( :excludeIds )',
                'id IN ( :courseIds )',
                'locked = :locked',
                'lessonNum > :lessonNumGT',
                'orgCode = :orgCode',
                'orgCode LIKE :likeOrgCode'
            )
        );
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        if (empty($conditions['status'])) {
            unset($conditions['status']);
        }

        if (empty($conditions['categoryIds'])) {
            unset($conditions['categoryIds']);
        }

        if (isset($conditions['likeOrgCode'])) {
            $conditions['likeOrgCode'] .= "%";
        }

        $builder = parent::_createQueryBuilder($conditions);

        if (isset($conditions['types'])) {
            $builder->andWhere('type IN ( :types )');
        }

        return $builder;
    }
}
