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
    
    public function findCoursesByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->getTablename()} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findCoursesByTagIds(array $tagIds, $status, $start, $limit)
    {
        if(empty($tagIds)){
            return array();
        }
       
        $sql ="SELECT * FROM {$this->getTablename()} WHERE status = '$status'";

        foreach ($tagIds as $tagId) {
            $sql .= " AND tags LIKE '%$tagId%' ";
        }

        $sql .= "ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->getConnection()->fetchAll($sql, $tagIds);
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

        if (isset($conditions['notFree'])) {
            $conditions['notFree'] = 0;
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from(self::TABLENAME, 'course')
            ->andWhere('status = :status')
            ->andWhere('price = :price')
            ->andWhere('price > :notFree')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('userId = :userId')
            ->andWhere('recommended = :recommended')
            ->andWhere('tags LIKE :tagsLike')
            ->andWhere('startTime >= :startTimeGreaterThan')
            ->andWhere('startTime < :startTimeLessThan')
            ->andWhere('vipLevelId >= :vipLevelIdGreaterThan');


        if (isset($conditions['categoryIds'])) {
            $categoryIds = array();
            foreach ($conditions['categoryIds'] as $categoryId) {
                if (ctype_digit((string)abs($categoryId))) {
                    $categoryIds[] = $categoryId;
                }
            }
            if ($categoryIds) {
                $categoryIds = join(',', $categoryIds);
                $builder->andStaticWhere("categoryId IN ($categoryIds)");
            }
        }

        if (isset($conditions['vipLevelIds'])) {
            $vipLevelIds = array();
            foreach ($conditions['vipLevelIds'] as $vipLevelId) {
                if (ctype_digit((string)abs($vipLevelId))) {
                    $vipLevelIds[] = $vipLevelId;
                }
            }
            if ($vipLevelIds) {
                $vipLevelIds = join(',', $vipLevelIds);
                $builder->andStaticWhere("vipLevelId IN ($vipLevelIds)");
            }

        }

        return $builder;
    }

    private function getTablename()
    {
        return self::TABLENAME;
    }
}