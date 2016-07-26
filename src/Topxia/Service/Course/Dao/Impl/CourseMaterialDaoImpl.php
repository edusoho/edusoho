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
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
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

    public function deleteMaterialsByLessonId($lessonId, $courseType)
    {
        $sql = "DELETE FROM {$this->table} WHERE lessonId = ? AND type = ?";
        return $this->getConnection()->executeUpdate($sql, array($lessonId, $courseType));
    }

    public function deleteMaterialsByCourseId($courseId, $courseType)
    {
        $sql = "DELETE FROM {$this->table} WHERE courseId = ? AND type = ?";
        return $this->getConnection()->executeUpdate($sql, array($courseId, $courseType));
    }

    public function deleteMaterialsByFileId($fileId)
    {
        return $this->getConnection()->delete($this->table, array('fileId' => $fileId));
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

    public function searchMaterialsGroupByFileId($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->groupBy('fileId')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchMaterialCountGroupByFileId($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(DISTINCT(fileId))');
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