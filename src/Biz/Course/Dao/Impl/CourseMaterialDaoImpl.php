<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseMaterialDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseMaterialDaoImpl extends GeneralDaoImpl implements CourseMaterialDao
{
    protected $table = 'course_material';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys'   => array('createdTime'),
            'conditions' => array(
                'id = :id',
                'courseId = :courseId',
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
                'courseId IN (:courseIds)'
            )
        );
    }


    public function findMaterialsByCopyIdAndLockedCourseIds($copyId, $courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';

        $parmaters = array_merge(array($copyId), $courseIds);

        $sql = "SELECT * FROM {$this->table()} WHERE copyId= ? AND courseId IN ({$marks})";

        return $this->db()->fetchAll($sql, $parmaters) ?: array();
    }

    public function deleteMaterialsByLessonId($lessonId, $courseType)
    {
        return $this->db()->delete($this->table(), array('lessonId' => $lessonId, 'type' => $courseType));
    }

    public function deleteMaterialsByCourseId($courseId, $courseType)
    {
        return $this->db()->delete($this->table(), array('courseId' => $courseId, 'type' => $courseType));
    }

    public function deleteMaterialsByFileId($fileId)
    {
        return $this->getConnection()->delete($this->table, array('fileId' => $fileId));
    }

    public function searchMaterialsGroupByFileId($conditions, $orderBy, $start, $limit)
    {
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

        return $this->_createQueryBuilder($conditions);
    }
}