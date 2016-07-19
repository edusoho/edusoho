<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseMaterialDao;

class CourseMaterialDaoImpl extends BaseDao implements CourseMaterialDao
{
    protected $table = 'course_material';

    public function getMaterial($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findMaterialsByCourseId($courseId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql ="SELECT * FROM {$this->table} WHERE courseId=? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($courseId)) ? : array();
    }

    public function findMaterialsByLessonId($lessonId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql ="SELECT * FROM {$this->table} WHERE lessonId=? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($lessonId)) ? : array();
    }

    public function getMaterialCountByCourseId($courseId)
    {
        $sql ="SELECT COUNT(*) FROM {$this->table} WHERE courseId = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId));
    }

    public function addMaterial($material)
    {
        $affected = $this->getConnection()->insert($this->table, $material);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert material error.');
        }
        return $this->getMaterial($this->getConnection()->lastInsertId());
    }

    public function updateMaterial($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getMaterial($id);
    }

    public function findMaterialsByCopyIdAndLockedCourseIds($copyId, $courseIds)
    {
        if(empty($courseIds)){
            return array();
        }
       
        $marks = str_repeat('?,', count($courseIds) - 1) . '?';
       
        $parmaters = array_merge(array($copyId), $courseIds);

        $sql ="SELECT * FROM {$this->table} WHERE copyId= ? AND courseId IN ({$marks})";
        
        return $this->getConnection()->fetchAll($sql, $parmaters) ? : array();
    }

    public function deleteMaterial($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteMaterialsByLessonId($lessonId)
    {
        $sql = "DELETE FROM {$this->table} WHERE lessonId = ?";
        return $this->getConnection()->executeUpdate($sql, array($lessonId));
    }

    public function deleteMaterialsByCourseId($courseId)
    {
        $sql = "DELETE FROM {$this->table} WHERE courseId = ?";
        return $this->getConnection()->executeUpdate($sql, array($courseId));
    }

    public function deleteMaterialsByFileId($fileId)
    {
        return $this->getConnection()->delete($this->table, array('fileId' => $fileId));
    }

    public function getLessonMaterialCount($courseId,$lessonId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  courseId = ? AND lessonId = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId, $lessonId)); 
    } 

    public function getMaterialCountByFileId($fileId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE  fileId = ? ";
        return $this->getConnection()->fetchColumn($sql, array($fileId)); 
    }

    public function findMaterialsGroupByFileId($courseId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? and fileId != 0 GROUP BY fileId ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($courseId)) ? : array();
    }

    public function findMaterialCountGroupByFileId($courseId)
    {
        $sql = "SELECT COUNT(DISTINCT(fileId)) FROM {$this->table} WHERE courseId = ? and fileId != 0 ";
        return $this->getConnection()->fetchColumn($sql, array($courseId),0); 
    }

    public function searchMaterials($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchMaterialCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)

            ->from($this->table, $this->table)
            ->andWhere('id = :id')
            ->andWhere('courseId = :courseId')
            ->andWhere('lessonId = :lessonId')
            ->andWhere('lessonId <> ( :excludeLessonId )')
            ->andWhere('type = :type')
            ->andWhere('userId = :userId')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('copyId = :copyId')
            ->andWhere('fileId = :fileId')
            ->andWhere('fileId IN (:fileIds)')
            ->andWhere('source = :source')
            ->andWhere('source IN (:sources)')
            ->andWhere('courseId IN (:courseIds)');

        return $builder;
    }
}