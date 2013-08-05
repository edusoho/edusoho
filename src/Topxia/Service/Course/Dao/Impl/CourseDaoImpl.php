<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseDao;

class CourseDaoImpl extends BaseDao implements CourseDao
{
    protected $table = 'course';

    public function getCourse($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }
    
    public function findCoursesByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchCourses($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchCourseCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function _createSearchQueryBuilder($conditions)
    {

        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        if (isset($conditions['locationId'])) {
            $locationId = (int) $conditions['locationId'];
            if (!empty($locationId)) {
                $conditions['locationIdLike'] = rtrim($conditions['locationId'], '0') . '%';
            }
            unset($conditions['locationId']);
        }

        if (isset($conditions['tagId'])) {
            $tagId = (int) $conditions['tagId'];
            if (!empty($tagId)) {
                $conditions['tagsLike'] = "%|{$conditions['tagId']}|%";
            }
            unset($conditions['tagId']);
        }

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course')
            ->andWhere('status = :status')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('userId = :userId')
            ->andWhere('locationId LIKE :locationIdLike')
            ->andWhere('tagId LIKE :tagsLike')
            ->andWhere('startTime >= :startTimeGreaterThan')
            ->andWhere('startTime < :startTimeLessThan');
    }

    public function addCourse($course)
    {
        $affected = $this->getConnection()->insert($this->table, $course);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course error.');
        }
        return $this->getCourse($this->getConnection()->lastInsertId());
    }

    public function updateCourse($id, $fields)
    {
        $id = $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getCourse($id);
    }

    public function deleteCourse($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }
}