<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseDao;

class CourseDaoImpl extends BaseDao implements CourseDao
{

    public function getCourse($id)
    {
        $sql = "SELECT * FROM {$this->getTablename()} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getLessonByCourseIdAndNumber($courseId, $number)
    {
        $sql = "SELECT * FROM {$this->getTablename()} WHERE courseId = ? AND number = ? LIMIT 1";
        return $this->getConnection()->fetchAll($sql, array($courseId, $number)) ? : null;
    }

    public function getCoursesCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->getTablename()}";
        return $this->getConnection()->fetchColumn($sql) ? : null;
    }
    
    public function findCoursesByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->getTablename()} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findCoursesByCourseIds(array $ids, $start, $limit)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->getTablename()} WHERE id IN ({$marks}) LIMIT {$start}, {$limit};";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findCoursesByLikeTitle($title)
    {
        if(empty($title)){
            return array();
        }
        $sql ="SELECT * FROM {$this->getTablename()} WHERE `title` LIKE ?; ";
        return $this->getConnection()->fetchAll($sql, array('%'.$title.'%'));
    }

    public function findCoursesByTagIdsAndStatus(array $tagIds, $status, $start, $limit)
    {

        $sql ="SELECT * FROM {$this->getTablename()} WHERE status = ?";

        foreach ($tagIds as $tagId) {
                $sql .= " AND tags LIKE '%|$tagId|%'";
        }

        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        
        return $this->getConnection()->fetchAll($sql, array($status));
    }

    public function findCoursesByAnyTagIdsAndStatus(array $tagIds, $status, $orderBy, $start, $limit)
    {
        if(empty($tagIds)){
            return array();
        }

        $sql ="SELECT * FROM {$this->getTablename()} WHERE status = ? AND ";

        foreach ($tagIds as $key => $tagId) {
            if ($key > 0 ) {
                $sql .= "OR tags LIKE '%|$tagId|%'";
            } else {
                $sql .= " tags LIKE '%|$tagId|%' ";
            }
        }

        $sql .= " ORDER BY {$orderBy[0]} {$orderBy[1]} LIMIT {$start}, {$limit}";
        
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
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchCourseCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addCourse($course)
    {
        $affected = $this->getConnection()->insert(self::TABLENAME, $course);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course error.');
        }
        return $this->getCourse($this->getConnection()->lastInsertId());
    }

    public function updateCourse($id, $fields)
    {
        $this->getConnection()->update(self::TABLENAME, $fields, array('id' => $id));
        return $this->getCourse($id);
    }

    public function deleteCourse($id)
    {
        return $this->getConnection()->delete(self::TABLENAME, array('id' => $id));
    }

    public function waveCourse($id,$field,$diff)
    {
        $fields = array('hitNum');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }
        $sql = "UPDATE {$this->getTablename()} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";
        
        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    private function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        if (isset($conditions['tagId'])) {
            $tagId = (int) $conditions['tagId'];
            if (!empty($tagId)) {
                $conditions['tagsLike'] = "%|{$conditions['tagId']}|%";
            }
            unset($conditions['tagId']);
        }

        if (isset($conditions['tagIds'])) {
            $tagIds = $conditions['tagIds'];
            $conditions['tagsLike'] = '%|';
            if (!empty($tagIds)) {
                foreach ($tagIds as $tagId) {
                    $conditions['tagsLike'] .= "{$tagId}|";
                }
            }
            $conditions['tagsLike'] .= '%';
            unset($conditions['tagIds']);
        }
        
        if (isset($conditions['notFree'])) {
            $conditions['notFree'] = 0;
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from(self::TABLENAME, 'course')
            ->andWhere('status = :status')
            ->andWhere('type = :type')
            ->andWhere('price = :price')
            ->andWhere('price > :notFree')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('userId = :userId')
            ->andWhere('recommended = :recommended')
            ->andWhere('tags LIKE :tagsLike')
            ->andWhere('startTime >= :startTimeGreaterThan')
            ->andWhere('startTime < :startTimeLessThan')
            ->andWhere('freeStartTime < :freeStartTimeLessThan')
            ->andWhere('freeEndTime > :freeEndTimeGreaterThan')
            ->andWhere('freeStartTime > :freeStartTimeGreaterThan')
            ->andWhere('rating > :ratingGreaterThan')
            ->andWhere('vipLevelId >= :vipLevelIdGreaterThan')
            ->andWhere('vipLevelId = :vipLevelId')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime')
            ->andWhere('categoryId = :categoryId')
            ->andWhere('smallPicture = :smallPicture')
            ->andWhere('categoryId IN ( :categoryIds )')
            ->andWhere('vipLevelId IN ( :vipLevelIds )')
            ->andWhere('id NOT IN ( :courseIds )');

        return $builder;
    }

    public function analysisCourseDataByTime($startTime,$endTime)
    {
             $sql="SELECT count( id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->getTablename()}` WHERE  `createdTime`>={$startTime} and `createdTime`<={$endTime} group by from_unixtime(`createdTime`,'%Y-%m-%d') order by date ASC ";

            return $this->getConnection()->fetchAll($sql);
    }

    public function findCoursesCountByLessThanCreatedTime($endTime)
    {
        $sql="SELECT count(id) as count FROM `{$this->getTablename()}` WHERE `createdTime`<={$endTime} ";

        return $this->getConnection()->fetchColumn($sql);
    }

    public function analysisCourseSumByTime($endTime)
    {
         $sql="SELECT date , max(a.Count) as count from (SELECT from_unixtime(o.createdTime,'%Y-%m-%d') as date,( SELECT count(id) as count FROM  `{$this->getTablename()}`   i   WHERE   i.createdTime<=o.createdTime  )  as Count from `{$this->getTablename()}`  o  where o.createdTime<={$endTime} order by 1,2) as a group by date ";
         return $this->getConnection()->fetchAll($sql);
    }

    public function updateCoinPrice($cashRate)
    {
        $sql="UPDATE `{$this->getTablename()}` SET coinPrice = price*? WHERE coinPrice=0 ;";
        $this->getConnection()->executeUpdate($sql, array($cashRate));
    }

    private function getTablename()
    {
        return self::TABLENAME;
    }
}