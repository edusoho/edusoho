<?php

namespace Topxia\Service\OpenCourse\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\OpenCourse\Dao\OpenCourseDao;

class OpenCourseDaoImpl extends BaseDao implements OpenCourseDao
{
    protected $table = 'open_course';

    public $serializeFields = array(
        'teacherIds' => 'saw',
        'tags'       => 'saw'
    );

    public function getCourse($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql    = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            $course = $that->getConnection()->fetchAssoc($sql, array($id));

            return $course ? $that->createSerializer()->unserialize($course, $that->serializeFields) : null;
        }
        );
    }

    public function findCoursesByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks   = str_repeat('?,', count($ids) - 1).'?';
        $sql     = "SELECT * FROM {$this->getTable()} WHERE id IN ({$marks});";
        $courses = $this->getConnection()->fetchAll($sql, $ids);

        return $courses ? $this->createSerializer()->unserializes($courses, $this->serializeFields) : null;
    }

    public function searchCourses($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime', 'recommendedSeq', 'studentNum', 'hitNum'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $courses = $builder->execute()->fetchAll();
        return $courses ? $this->createSerializer()->unserializes($courses, $this->serializeFields) : array();
    }

    public function searchCourseCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addCourse($course)
    {
        $course = $this->createSerializer()->serialize($course, $this->serializeFields);

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
        $fields                = $this->createSerializer()->serialize($fields, $this->serializeFields);

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
        $fields = array('postNum', 'hitNum', 'lessonNum', 'collectNum', 'likeNum');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }

        $currentTime = time();

        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";
        $this->getConnection()->executeQuery($sql, array($diff, $id));

        $current = $this->getCourse($id);
        if($currentTime - $current['updatedTime'] > (60 * 10)){  //十分钟清一次缓存
            $sql = "UPDATE {$this->table} SET updatedTime = '{$currentTime}' WHERE id = ? LIMIT 1";
            $this->getConnection()->executeQuery($sql, array($id));
            $this->clearCached();
        }

        return $current;
    }

    protected function _createSearchQueryBuilder($conditions)
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

        if (empty($conditions['status']) || $conditions['status'] == "") {
            unset($conditions['status']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)

            ->from($this->table, 'open_course')
            ->andWhere('updatedTime >= :updatedTime_GE')
            ->andWhere('status = :status')
            ->andWhere('type = :type')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('userId = :userId')
            ->andWhere('tags LIKE :tagsLike')
            ->andWhere('startTime >= :startTimeGreaterThan')
            ->andWhere('startTime < :startTimeLessThan')
            ->andWhere('rating > :ratingGreaterThan')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime')
            ->andWhere('categoryId = :categoryId')
            ->andWhere('smallPicture = :smallPicture')
            ->andWhere('categoryId IN ( :categoryIds )')
            ->andWhere('parentId = :parentId')
            ->andWhere('parentId > :parentId_GT')
            ->andWhere('parentId IN ( :parentIds )')
            ->andWhere('id NOT IN ( :excludeIds )')
            ->andWhere('id IN ( :courseIds )')
            ->andWhere('recommended = :recommended')
            ->andWhere('locked = :locked');

        if (isset($conditions['tagIds'])) {
            $tagIds = $conditions['tagIds'];

            foreach ($tagIds as $key => $tagId) {
                $conditions['tagIds_'.$key] = '%|'.$tagId.'|%';
                $builder->andWhere('tags LIKE :tagIds_'.$key);
            }

            unset($conditions['tagIds']);
        }

        return $builder;
    }
}
