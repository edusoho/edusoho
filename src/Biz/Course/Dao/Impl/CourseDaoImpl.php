<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\Framework\Dao\DaoException;

class CourseDaoImpl extends AdvancedDaoImpl implements CourseDao
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

    public function searchWithJoinCourseSet($conditions, $orderBys, $start, $limit)
    {
        $builder = $this->createJoinCourseSetQueryBuilder($conditions)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->select($this->table.'.*');

        $declares = $this->declares();

        foreach ($orderBys ?: array() as $order => $sort) {
            $this->checkOrderBy($order, $sort, $declares['orderbys']);
            $builder->addOrderBy($this->table.'.'.$order, $sort);
        }
        $result = $builder->execute()->fetchAll();

        return $result;
    }

    public function countWithJoinCourseSet($conditions)
    {
        $builder = $this->createJoinCourseSetQueryBuilder($conditions)
            ->select('COUNT(*)');

        return (int) $builder->execute()->fetchColumn(0);
    }

    // select cv.* from  (SELECT * FROM course_v8 WHERE 1=1)  cv inner join (select courseId,count(*) co from course_member where createdtime > 1534694400 and createdTime< 1535385600 group by courseId) cm on cv.id=cm.courseId order by cm.co desc LIMIT 0,8

    public function searchByStudentNumAndTimeZone($conditions, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $params = array();
        $courseSql = $this->getCourseSql($conditions, $params);
        $courseMemberSql = $this->getCourseMemberSql($conditions, $params);

        $sql = "SELECT cv.* FROM ($courseSql) cv LEFT JOIN ($courseMemberSql) cm ON cv.id=cm.courseId ORDER BY cm.co DESC,cv.createdTime DESC LIMIT $start,$limit";

        return $this->db()->fetchAll($sql, $params) ?: array();
    }

    public function searchByRatingAndTimeZone($conditions, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $params = array();
        $courseSql = $this->getCourseSql($conditions, $params);
        $courseReviewSql = $this->getCourseReviewSql($conditions, $params);

        $sql = "SELECT cv.* FROM ($courseSql) cv LEFT JOIN ($courseReviewSql) cm ON cv.id=cm.courseId ORDER BY cm.co DESC,cv.createdTime DESC LIMIT $start,$limit";

        return $this->db()->fetchAll($sql, $params) ?: array();
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
                'seq',
                'price',
                'parentId',
            ),
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'course_v8.id = :id',
                'course_v8.courseSetId = :courseSetId',
                'course_v8.courseSetId IN (:courseSetIds)',
                'course_v8.updatedTime >= :updatedTime_GE',
                'course_v8.status = :status',
                'course_v8.type = :type',
                'course_v8.price = :price',
                'course_v8.price > :price_GT',
                'course_v8.price >= :price_GE',
                'course_v8.originPrice > :originPrice_GT',
                'course_v8.originPrice >= :originPrice_GE',
                'course_v8.originPrice = :originPrice',
                'course_v8.coinPrice > :coinPrice_GT',
                'course_v8.coinPrice = :coinPrice',
                'course_v8.originCoinPrice > :originCoinPrice_GT',
                'course_v8.originCoinPrice = :originCoinPrice',
                'course_v8.title LIKE :titleLike',
                'course_v8.courseSetTitle LIKE :courseSetTitleLike',
                'course_v8.userId = :userId',
                'course_v8.recommended = :recommended',
                'course_v8.createdTime >= :startTime',
                'course_v8.createdTime < :endTime',
                'course_v8.rating > :ratingGreaterThan',
                'course_v8.vipLevelId >= :vipLevelIdGreaterThan',
                'course_v8.vipLevelId = :vipLevelId',
                'course_v8.categoryId = :categoryId',
                'course_v8.smallPicture = :smallPicture',
                'course_v8.categoryId IN ( :categoryIds )',
                'course_v8.vipLevelId IN ( :vipLevelIds )',
                'course_v8.parentId = :parentId',
                'course_v8.parentId > :parentId_GT',
                'course_v8.parentId IN ( :parentIds )',
                'course_v8.id NOT IN ( :excludeIds )',
                'course_v8.id IN ( :courseIds )',
                'course_v8.id IN ( :ids)',
                'course_v8.locked = :locked',
                'course_v8.lessonNum > :lessonNumGT',
                'course_v8.orgCode = :orgCode',
                'course_v8.orgCode LIKE :likeOrgCode',
                'course_v8.buyable = :buyable',
                'concat(course_v8.courseSetTitle, course_v8.title) like :courseOrCourseSetTitleLike',
                'course_v8.type NOT IN (:excludeTypes)',
                'course_v8.type IN (:types)',
                'course_v8.courseType = :courseType',
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
            $builder->andWhere('course_v8.type IN ( :types )');
        }

        return $builder;
    }

    protected function createJoinCourseSetQueryBuilder($conditions)
    {
        $builder = $this->createQueryBuilder($conditions);
        $builder->innerJoin($this->table, 'course_set_v8', 'csv', 'csv.id = '.$this->table.'.courseSetId');

        $joinConditions = array(
            'csv.status = :courseSetStatus',
        );

        foreach ($joinConditions as $condition) {
            $builder->andWhere($condition);
        }

        return $builder;
    }

    protected function getCourseSql($conditions, &$params)
    {
        $courseSql = 'SELECT c.* FROM course_v8 c INNER JOIN course_set_v8 csv on c.courseSetId = csv.id WHERE 1=1 ';

        if (isset($conditions['parentId'])) {
            $courseSql .= ' AND c.parentId = ? ';
            $params[] = $conditions['parentId'];
        }

        if (isset($conditions['status'])) {
            $courseSql .= ' AND c.status = ? ';
            $params[] = $conditions['status'];
        }

        if (isset($conditions['excludeTypes'])) {
            $marks = str_repeat('?,', count($conditions['excludeTypes']) - 1).'?';
            $courseSql .= " AND c.type NOT IN ($marks)";
            $params = array_merge($params, $conditions['excludeTypes']);
        }

        if (isset($conditions['courseSetStatus'])) {
            $courseSql .= ' AND csv.status = ? ';
            $params[] = $conditions['courseSetStatus'];
        }

        if (!empty($conditions['categoryIds'])) {
            $marks = str_repeat('?,', count($conditions['categoryIds']) - 1).'?';
            $courseSql .= " AND c.categoryId IN ($marks)";
            $params = array_merge($params, $conditions['categoryIds']);
        }

        if (isset($conditions['type'])) {
            $courseSql .= ' AND c.type = ? ';
            $params[] = $conditions['type'];
        }

        if (isset($conditions['title'])) {
            $courseSql .= ' AND c.title LIKE ? ';
            $params[] = "%{$conditions['title']}%";
        }

        if (isset($conditions['courseSetTitle '])) {
            $courseSql .= ' AND c.courseSetTitle  LIKE ? ';
            $params[] = "%{$conditions['courseSetTitle ']}%";
        }

        return $courseSql;
    }

    protected function getCourseMemberSql($conditions, &$params)
    {
        $courseMemberSql = "SELECT courseId,count(id) co FROM course_member WHERE role='student' ";

        if (!empty($conditions['outerEndTime'])) {
            $courseMemberSql .= ' AND createdTime > ? AND createdTime < ? ';
            $params[] = $conditions['outerStartTime'];
            $params[] = $conditions['outerEndTime'];
        }
        $courseMemberSql .= ' GROUP BY courseId';

        return $courseMemberSql;
    }

    public function getCourseReviewSql($conditions, &$params)
    {
        $courseReviewSql = 'SELECT courseId,avg(rating) co FROM course_review WHERE 1=1 ';
        if (!empty($conditions['outerEndTime'])) {
            $courseReviewSql .= ' AND createdTime > ? AND createdTime < ? ';
            $params[] = $conditions['outerStartTime'];
            $params[] = $conditions['outerEndTime'];
        }
        $courseReviewSql .= ' GROUP BY courseId';

        return $courseReviewSql;
    }

    private function checkOrderBy($order, $sort, $allowOrderBys)
    {
        if (!in_array($order, $allowOrderBys, true)) {
            throw $this->createDaoException(
                sprintf("SQL order by field is only allowed '%s', but you give `{$order}`.", implode(',', $allowOrderBys))
            );
        }
        if (!in_array(strtoupper($sort), array('ASC', 'DESC'), true)) {
            throw $this->createDaoException("SQL order by direction is only allowed `ASC`, `DESC`, but you give `{$sort}`.");
        }
    }

    private function createDaoException($message = '', $code = 0)
    {
        return new DaoException($message, $code);
    }
}
