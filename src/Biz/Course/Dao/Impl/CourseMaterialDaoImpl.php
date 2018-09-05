<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseMaterialDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CourseMaterialDaoImpl extends AdvancedDaoImpl implements CourseMaterialDao
{
    protected $table = 'course_material_v8';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'id in (:ids)',
                'id = :id',
                'courseId = :courseId',
                'courseSetId = :courseSetId',
                'lessonId = :lessonId',
                'lessonId <> ( :excludeLessonId )',
                'type = :type',
                'userId = :userId',
                'title LIKE :titleLike',
                'copyId = :copyId',
                'fileId = :fileId',
                'fileId IN (:fileIds)',
                'source = :source',
                'source IN (:sources)',
                'courseSetId IN (:courseSetIds)',
                'courseId IN (:courseIds)',
            ),
        );
    }

    public function findByCopyIdAndLockedCourseIds($copyId, $courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';

        $parmaters = array_merge(array($copyId), $courseIds);

        $sql = "SELECT * FROM {$this->table()} WHERE copyId= ? AND courseId IN ({$marks})";

        return $this->db()->fetchAll($sql, $parmaters) ?: array();
    }

    public function deleteByLessonId($lessonId, $courseType)
    {
        return $this->db()->delete($this->table(), array('lessonId' => $lessonId, 'type' => $courseType));
    }

    public function deleteByCourseId($courseId, $courseType)
    {
        return $this->db()->delete($this->table(), array('courseId' => $courseId, 'type' => $courseType));
    }

    public function findMaterialsByLessonIdAndSource($lessonId, $source)
    {
        return $this->findByFields(array('lessonId' => $lessonId, 'source' => $source));
    }

    public function findMaterialsByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function deleteByCourseSetId($courseSetId, $courseType)
    {
        return $this->db()->delete($this->table(), array('courseSetId' => $courseSetId, 'type' => $courseType));
    }

    public function deleteByFileId($fileId)
    {
        return $this->db()->delete($this->table, array('fileId' => $fileId));
    }

    public function searchDistinctFileIds($conditions, $orderBys, $start, $limit)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('fileId, max(createdTime) AS `createdTime`')
            ->groupBy('fileId')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        foreach ($orderBys ?: array() as $field => $direction) {
            $builder->addOrderBy($field, $direction);
        }

        return $builder->execute()->fetchAll() ?: array();
    }

    public function countGroupByFileId($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(DISTINCT(fileId))');

        return $builder->execute()->fetchColumn(0);
    }

    protected function createQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        if (isset($conditions['titleLike'])) {
            $conditions['titleLike'] = "%{$conditions['titleLike']}%";
        }

        return parent::createQueryBuilder($conditions);
    }
}
