<?php

namespace Topxia\Service\Course\Dao\Impl;

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
}