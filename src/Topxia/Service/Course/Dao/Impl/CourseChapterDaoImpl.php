<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseChapterDao;

class CourseChapterDaoImpl extends BaseDao implements CourseChapterDao
{
    protected $table = 'course_chapter';

    public function getChapter($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addChapter(array $chapter)
    {
        $affected = $this->getConnection()->insert($this->table, $chapter);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course chapter error.');
        }
        return $this->getChapter($this->getConnection()->lastInsertId());
    }

    public function findChaptersByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? ORDER BY createdTime ASC";
        return $this->getConnection()->fetchAll($sql, array($courseId));
    }

    public function searchChapterCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchChapters($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
        ->select('*')
        ->orderBy($orderBy[0], $orderBy[1])
        ->setFirstResult($start)
        ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function getChapterCountByCourseIdAndType($courseId, $type)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  courseId = ? AND type = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId, $type));
    }

    public function getChapterCountByCourseIdAndTypeAndParentId($courseId, $type, $parentId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  courseId = ? AND type = ? AND parentId = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId, $type, $parentId));
    }

    public function getLastChapterByCourseIdAndType($courseId, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE  courseId = ? AND type = ? ORDER BY seq DESC LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($courseId, $type)) ? : null;
    }

    public function getLastChapterByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE  courseId = ? ORDER BY seq DESC LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($courseId)) ? : null;
    }

    public function getChapterMaxSeqByCourseId($courseId)
    {
        $sql = "SELECT MAX(seq) FROM {$this->table} WHERE  courseId = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId));
    }

    public function updateChapter($id, array $chapter)
    {
        $this->getConnection()->update($this->table, $chapter, array('id' => $id));
        return $this->getChapter($id);
    }

    public function deleteChapter($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteChaptersByCourseId($courseId)
    {
        $sql = "DELETE FROM {$this->table} WHERE courseId = ?";
        return $this->getConnection()->executeUpdate($sql, array($courseId));
    }

    public function findChaptersByChapterIdAndLockedCourseIds($pId, $courseIds)
    {
       if(empty($courseIds)){
            return array();
        }
       
        $marks = str_repeat('?,', count($courseIds) - 1) . '?';
       
        $parmaters = array_merge(array($pId), $courseIds);

        $sql ="SELECT * FROM {$this->table} WHERE pId= ? AND courseId IN ({$marks})";
        
        return $this->getConnection()->fetchAll($sql, $parmaters) ? : array();
    }

    protected function _createSearchQueryBuilder($conditions)
    {   
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course_chapter')
            ->andWhere('courseId = :courseId');
        return $builder;
    }

}