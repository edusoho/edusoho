<?php
namespace Biz\Testpaper\Dao\Impl;

use Biz\Testpaper\Dao\TestpaperDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TestpaperDaoImpl extends GeneralDaoImpl implements TestpaperDao
{
    protected $table = 'testpaper';

    public function findTestpapersByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findTestpapersByCopyIdAndLockedTarget($copyId, $lockedTarget)
    {
        $sql = "SELECT * FROM {$this->table} WHERE copyId = ?  AND target IN {$lockedTarget}";
        return $this->db()->fetchAll($sql, array($copyId));
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
        $declares['orderbys'] = array(
            'createdTime'
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
            'copyId > :copyIdGT'
        );

        $declares['serializes'] = array(
            'metas'           => 'json',
            'passedCondition' => 'json'
        );

        return $declares;
    }
}
