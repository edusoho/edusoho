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
        if (empty($ids)) {
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->db()->fetchAll($sql, $ids);
    }

    public function findTestpaperByTargets(array $targets)
    {
        if (empty($targets)) {
            return array();
        }
        $marks = str_repeat('?,', count($targets) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE target IN ({$marks})";

        return $this->db()->fetchAll($sql, $targets) ?: array();
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
            'target = :target',
            'target LIKE :targetLike',
            'status LIKE :status'
        );

        $declares['serializes'] = array(
            'metas'           => 'json',
            'passedCondition' => 'json'
        );

        return $declares;
    }
}
