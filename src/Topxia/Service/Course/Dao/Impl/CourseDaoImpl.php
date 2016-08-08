<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseDao;

class CourseDaoImpl extends BaseDao implements CourseDao
{
    protected $table = 'course';

    public function getCourse($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function getLessonByCourseIdAndNumber($courseId, $number)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:number:{$number}", $courseId, $number, function ($courseId, $number) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE courseId = ? AND number = ? LIMIT 1";
            return $that->getConnection()->fetchAll($sql, array($courseId, $number)) ?: null;
        }

        );
    }

    public function findCoursesByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->getTable()} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findCoursesByParentIdAndLocked($parentId, $locked)
    {
        $that = $this;

        return $this->fetchCached("parentId:{$parentId}:locked:{$locked}", $parentId, $locked, function ($parentId, $locked) use ($that) {
            if (empty($parentId)) {
                return array();
            }

            $sql = "SELECT * FROM {$that->getTable()} WHERE parentId = ? AND locked = ?";
            return $that->getConnection()->fetchAll($sql, array($parentId, $locked));
        }

        );
    }

    public function findCoursesByCourseIds(array $ids, $start, $limit)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->getTable()} WHERE id IN ({$marks}) LIMIT {$start}, {$limit};";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findCoursesByLikeTitle($title)
    {
        if (empty($title)) {
            return array();
        }

        $sql = "SELECT * FROM {$this->getTable()} WHERE `title` LIKE ?; ";
        return $this->getConnection()->fetchAll($sql, array('%'.$title.'%'));
    }

    public function findNormalCoursesByAnyTagIdsAndStatus(array $tagIds, $status, $orderBy, $start, $limit)
    {
        if (empty($tagIds)) {
            return array();
        }

        $sql = "SELECT * FROM {$this->getTable()} WHERE parentId = 0 AND status = ? AND (";

        foreach ($tagIds as $key => $tagId) {
            if ($key > 0) {
                $sql .= "OR tags LIKE '%|$tagId|%'";
            } else {
                $sql .= " tags LIKE '%|$tagId|%' ";
            }
        }

        $sql .= ") ORDER BY {$orderBy[0]} {$orderBy[1]} LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, array($status));
    }

    public function searchCourses($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        if ($orderBy[0] == 'recommendedSeq') {
            $builder->addOrderBy('recommendedTime', 'DESC');
        }

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchCourseCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addCourse($course)
    {
        $course['createdTime'] = time();
        $course['updatedTime'] = $course['createdTime'];
        $affected              = $this->getConnection()->insert($this->table, $course);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert course error.');
        }

        return $this->getCourse($this->getConnection()->lastInsertId());
    }

    public function updateCourse($id, $fields)
    {
        $fields['updatedTime'] = time();
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getCourse($id);
    }

    public function deleteCourse($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function waveCourse($id, $field, $diff)
    {
        $fields = array('hitNum', 'noteNum');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }

        $currentTime = time();

        $sql = "UPDATE {$this->getTable()} SET {$field} = {$field} + ?, updatedTime = '{$currentTime}' WHERE id = ? LIMIT 1";

        $result = $this->getConnection()->executeQuery($sql, array($diff, $id));
        $this->clearCached();
        return $result;
    }

    public function clearCourseDiscountPrice($discountId)
    {
        $currentTime = time();
        $sql         = "UPDATE course SET updatedTime = '{$currentTime}', price = originPrice, discountId = 0, discount = 10 WHERE discountId = ?";
        $result      = $this->getConnection()->executeQuery($sql, array($discountId));
        $this->clearCached();
        return $result;
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        if (!empty($conditions['tags'])) {
            $tagIds = $conditions['tags'];
            $tags   = '';

            foreach ($tagIds as $tagId) {
                $tags .= "|".$tagId;
            }

            $conditions['tags'] = $tags."|";
        }

        if (isset($conditions['tagId'])) {
            $tagId = (int) $conditions['tagId'];

            if (!empty($tagId)) {
                $conditions['tagsLike'] = "%|{$conditions['tagId']}|%";
            }

            unset($conditions['tagId']);
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

        $builder = $this->createDynamicQueryBuilder($conditions)

            ->from($this->table, 'course')
            ->andWhere('updatedTime >= :updatedTime_GE')
            ->andWhere('status = :status')
            ->andWhere('type = :type')
            ->andWhere('price = :price')
            ->andWhere('price > :price_GT')
            ->andWhere('originPrice > :originPrice_GT')
            ->andWhere('originPrice = :originPrice')
            ->andWhere('coinPrice > :coinPrice_GT')
            ->andWhere('coinPrice = :coinPrice')
            ->andWhere('originCoinPrice > :originCoinPrice_GT')
            ->andWhere('originCoinPrice = :originCoinPrice')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('userId = :userId')
            ->andWhere('recommended = :recommended')
            ->andWhere('tags LIKE :tagsLike')
            ->andWhere('startTime >= :startTimeGreaterThan')
            ->andWhere('startTime < :startTimeLessThan')
            ->andWhere('rating > :ratingGreaterThan')
            ->andWhere('vipLevelId >= :vipLevelIdGreaterThan')
            ->andWhere('vipLevelId = :vipLevelId')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime')
            ->andWhere('categoryId = :categoryId')
            ->andWhere('smallPicture = :smallPicture')
            ->andWhere('categoryId IN ( :categoryIds )')
            ->andWhere('vipLevelId IN ( :vipLevelIds )')
            ->andWhere('parentId = :parentId')
            ->andWhere('parentId > :parentId_GT')
            ->andWhere('parentId IN ( :parentIds )')
            ->andWhere('id NOT IN ( :excludeIds )')
            ->andWhere('id IN ( :courseIds )')
            ->andWhere('locked = :locked')
            ->andWhere('lessonNum > :lessonNumGT')
            ->andWhere('orgCode = :orgCode')
            ->andWhere('orgCode LIKE :likeOrgCode');

        if (isset($conditions['tagIds'])) {
            $tagIds = $conditions['tagIds'];

            foreach ($tagIds as $key => $tagId) {
                $conditions['tagIds_'.$key] = '%|'.$tagId.'|%';
                $builder->andWhere('tags LIKE :tagIds_'.$key);
            }

            unset($conditions['tagIds']);
        }

        if (isset($conditions['types'])) {
            $builder->andWhere('type IN ( :types )');
        }

        return $builder;
    }

    public function analysisCourseDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count( id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->getTable()}` WHERE  `createdTime`>={$startTime} AND `createdTime`<={$endTime} group by from_unixtime(`createdTime`,'%Y-%m-%d') order by date ASC ";

        return $this->getConnection()->fetchAll($sql);
    }

    public function findCoursesCountByLessThanCreatedTime($endTime)
    {
        $sql = "SELECT count(id) as count FROM `{$this->getTable()}` WHERE `createdTime`<={$endTime} ";

        return $this->getConnection()->fetchColumn($sql);
    }

    public function analysisCourseSumByTime($endTime)
    {
        $sql = "SELECT date , max(a.Count) as count from (SELECT from_unixtime(o.createdTime,'%Y-%m-%d') as date,( SELECT count(id) as count FROM  `{$this->getTable()}`   i   WHERE   i.createdTime<=o.createdTime and i.parentId = 0)  as Count from `{$this->getTable()}`  o  where o.createdTime<={$endTime} order by 1,2) as a group by date ";
        return $this->getConnection()->fetchAll($sql);
    }
}
