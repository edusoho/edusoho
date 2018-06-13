<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseDaoImpl extends GeneralDaoImpl implements CourseDao
{
    protected $table = 'course_v8';

    public function findCoursesByParentIdAndLocked($parentId, $locked)
    {
        return $this->findByFields(array('parentId' => $parentId, 'locked' => $locked));
    }

    public function findCoursesByParentIds($parentIds)
    {
        return $this->findInField('parentId', $parentIds);
    }

    public function findCoursesByCourseSetIdAndStatus($courseSetId, $status = null)
    {
        if (empty($status)) {
            return $this->findByFields(array('courseSetId' => $courseSetId));
        }

        return $this->findByFields(array('courseSetId' => $courseSetId, 'status' => $status));
    }

    public function getDefaultCourseByCourseSetId($courseSetId)
    {
        return $this->getByFields(array('courseSetId' => $courseSetId, 'isDefault' => 1));
    }

    public function getDefaultCoursesByCourseSetIds($courseSetIds)
    {
        if (empty($courseSetIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseSetIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE isDefault=1 AND courseSetId IN ({$marks});";

        return $this->db()->fetchAll($sql, $courseSetIds);
    }

    public function findByCourseSetIds(array $setIds)
    {
        return $this->findInField('courseSetId', $setIds);
    }

    public function findCoursesByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findPriceIntervalByCourseSetIds($courseSetIds)
    {
        if (empty($courseSetIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($courseSetIds) - 1).'?';

        $sql = "SELECT MIN(price) AS minPrice, MAX(price) AS maxPrice,courseSetId FROM {$this->table} WHERE courseSetId IN ({$marks}) GROUP BY courseSetId";

        return $this->db()->fetchAll($sql, $courseSetIds) ?: null;
    }

    public function countGroupByCourseSetIds($courseSetIds)
    {
        if (empty($courseSetIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($courseSetIds) - 1).'?';

        $sql = "SELECT count(id) as 'courseNum', courseSetId FROM {$this->table} WHERE courseSetId IN ({$marks}) GROUP BY courseSetId";

        return $this->db()->fetchAll($sql, $courseSetIds) ?: null;
    }

    public function findCourseSetIncomesByCourseSetIds(array $courseSetIds)
    {
        if (empty($courseSetIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseSetIds) - 1).'?';
        $sql = "SELECT courseSetId,sum(`income`) as income FROM {$this->table} WHERE courseSetId IN ({$marks}) group by courseSetId;";

        return $this->db()->fetchAll($sql, $courseSetIds);
    }

    public function analysisCourseDataByTime($startTime, $endTime)
    {
        $conditions = array(
            'startTime' => $startTime,
            'endTime' => $endTime,
            'parentId' => 0,
        );

        $builder = $this->createQueryBuilder($conditions)
            ->select("count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date")
            ->groupBy("from_unixtime(createdTime,'%Y-%m-%d')")
            ->addOrderBy('date', 'asc');

        return $builder->execute()->fetchAll();
    }

    public function getMinAndMaxPublishedCoursePriceByCourseSetId($courseSetId)
    {
        $sql = "SELECT ifnull(min(price),0) as minPrice, ifnull(max(price),0) as maxPrice FROM {$this->table} WHERE courseSetId = {$courseSetId} and status = 'published'";

        return $this->db()->fetchAssoc($sql);
    }

    public function updateMaxRateByCourseSetId($courseSetId, $updateFields)
    {
        $this->db()->update($this->table, $updateFields, array('courseSetId' => $courseSetId));
    }

    public function updateCourseRecommendByCourseSetId($courseSetId, $fields)
    {
        $this->db()->update($this->table, $fields, array('courseSetId' => $courseSetId));
    }

    public function updateCategoryByCourseSetId($courseSetId, $fields)
    {
        $this->db()->update($this->table, $fields, array('courseSetId' => $courseSetId));
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'goals' => 'delimiter',
                'audiences' => 'delimiter',
                'services' => 'delimiter',
                'teacherIds' => 'delimiter',
            ),
            'orderbys' => array(
                'hitNum',
                'recommendedTime',
                'rating',
                'studentNum',
                'recommendedSeq',
                'createdTime',
                'originPrice',
                'updatedTime',
                'id',
                'price',
                'parentId',
            ),
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'courseSetId = :courseSetId',
                'courseSetId IN (:courseSetIds)',
                'updatedTime >= :updatedTime_GE',
                'status = :status',
                'type = :type',
                'price = :price',
                'price > :price_GT',
                'price >= :price_GE',
                'originPrice > :originPrice_GT',
                'originPrice >= :originPrice_GE',
                'originPrice = :originPrice',
                'coinPrice > :coinPrice_GT',
                'coinPrice = :coinPrice',
                'originCoinPrice > :originCoinPrice_GT',
                'originCoinPrice = :originCoinPrice',
                'title LIKE :titleLike',
                'courseSetTitle LIKE :courseSetTitleLike',
                'userId = :userId',
                'recommended = :recommended',
                'createdTime >= :startTime',
                'createdTime < :endTime',
                'rating > :ratingGreaterThan',
                'vipLevelId >= :vipLevelIdGreaterThan',
                'vipLevelId = :vipLevelId',
                'categoryId = :categoryId',
                'smallPicture = :smallPicture',
                'categoryId IN ( :categoryIds )',
                'vipLevelId IN ( :vipLevelIds )',
                'parentId = :parentId',
                'parentId > :parentId_GT',
                'parentId IN ( :parentIds )',
                'id NOT IN ( :excludeIds )',
                'id IN ( :courseIds )',
                'id IN ( :ids)',
                'locked = :locked',
                'lessonNum > :lessonNumGT',
                'orgCode = :orgCode',
                'orgCode LIKE :likeOrgCode',
                'buyable = :buyable',
                'concat(courseSetTitle, title) like :courseOrCourseSetTitleLike',
                'type NOT IN (:excludeTypes)',
                'type IN (:types)',
            ),
            'wave_cahceable_fields' => array('hitNum'),
        );
    }

    protected function createQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "{$conditions['title']}";
            unset($conditions['title']);
        }

        if (isset($conditions['courseSetTitle'])) {
            $conditions['courseSetTitleLike'] = "{$conditions['courseSetTitle']}";
            unset($conditions['courseSetTitle']);
        }

        if (empty($conditions['status'])) {
            unset($conditions['status']);
        }

        if (empty($conditions['categoryIds'])) {
            unset($conditions['categoryIds']);
        }

        if (isset($conditions['likeOrgCode'])) {
            $conditions['likeOrgCode'] .= '%';
        }
        $builder = parent::createQueryBuilder($conditions);

        if (isset($conditions['types'])) {
            $builder->andWhere('type IN ( :types )');
        }

        return $builder;
    }
}
