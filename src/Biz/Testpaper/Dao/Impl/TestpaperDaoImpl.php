<?php

namespace Biz\Testpaper\Dao\Impl;

use Biz\Testpaper\Dao\TestpaperDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class TestpaperDaoImpl extends AdvancedDaoImpl implements TestpaperDao
{
    protected $table = 'testpaper_v8';

    public function getByIdAndType($id, $type)
    {
        return $this->getByFields(array('id' => $id, 'type' => $type));
    }

    public function findTestpapersByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findTestpapersByIdsAndType($ids, $type)
    {
        $marks = str_repeat('?,', count($ids) - 1).'?';

        $sql = "select * from {$this->table()} where id in ({$marks}) and type = ?";
        $params = $ids;
        $params[] = $type;

        return $this->db()->fetchAll($sql, $params);
    }

    public function findTestpapersByCopyIdAndCourseSetIds($copyId, $courseSetIds)
    {
        if (empty($courseSetIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseSetIds) - 1).'?';

        $parmaters = array_merge(array($copyId), $courseSetIds);

        $sql = "SELECT * FROM {$this->table()} WHERE copyId= ? AND courseSetId IN ({$marks})";

        return $this->db()->fetchAll($sql, $parmaters) ?: array();
    }

    public function getTestpaperByCopyIdAndCourseSetId($copyId, $courseSetId)
    {
        return $this->getByFields(array('copyId' => $copyId, 'courseSetId' => $courseSetId));
    }

    public function deleteByCourseSetId($courseSetId)
    {
        return $this->db()->delete($this->table(), array('courseSetId' => $courseSetId));
    }

    public function declares()
    {
        $declares = array(
            'timestamps' => array('createdTime', 'updatedTime'),
        );

        $declares['orderbys'] = array(
            'createdTime',
        );

        $declares['conditions'] = array(
            'courseSetId = :courseSetId',
            'courseId = :courseId',
            'courseId IN (:courseIds)',
            'status = :status',
            'type = :type',
            'type IN (:types)',
            'id IN (:ids)',
            'copyId = :copyId',
            'copyId > :copyIdGT',
            'lessonId = :lessonId',
        );

        $declares['serializes'] = array(
            'metas' => 'json',
            'passedCondition' => 'json',
            'questionTypeSeq' => 'delimiter',
        );

        return $declares;
    }
}
