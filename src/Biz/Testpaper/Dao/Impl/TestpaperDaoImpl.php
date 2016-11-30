<?php
namespace Biz\Testpaper\Dao\Impl;

use Biz\Testpaper\Dao\TestpaperDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TestpaperDaoImpl extends GeneralDaoImpl implements TestpaperDao
{
    protected $table = 'testpaper';

    private $serializeFields = array(
        'metas' => 'json'
    );

    public function findTestpapersByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findTestpapersByCopyIdAndLockedTarget($copyId, $lockedTarget)
    {
        $sql = "SELECT * FROM {$this->table} WHERE copyId = ?  AND target IN {$lockedTarget}";
        return $this->db()->fetchAll($sql, array($copyId));
    }

    public function declares()
    {
        $declares['orderbys'] = array(
            'createdTime'
        );

        $declares['conditions'] = array(
            'courseId = :courseId',
            'courseId IN (:courseIds)',
            'status = :status',
            'type = :type'
        );

        $declares['serializes'] = array(
            'metas'           => 'json',
            'passedCondition' => 'json'
        );

        return $declares;
    }
}
