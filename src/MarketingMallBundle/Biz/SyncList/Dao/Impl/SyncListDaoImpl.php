<?php

namespace MarketingMallBundle\Biz\SyncList\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use MarketingMallBundle\Biz\SyncList\Dao\SyncListDao;

class SyncListDaoImpl extends GeneralDaoImpl implements SyncListDao
{
    protected $table = 'sync_list';

    public function getSyncType()
    {
        $sql = "SELECT type FROM {$this->table} where status = 'new' group by type";

        return $this->db()->fetchAll($sql);
    }

    public function getSyncIds()
    {
        $sql = "SELECT id FROM {$this->table} where status = 'new' ";

        return $this->db()->fetchAll($sql) ?: [];
    }

    public function getSyncDataId($id)
    {
        $sql = "SELECT id,type,status FROM {$this->table} where data = ? ";

        return $this->db()->fetchAll($sql, [$id]) ?: [];
    }

    public function SyncStatusUpdate($ids)
    {
        if (empty($ids)) {
            return [];
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "UPDATE {$this->table} SET status = 'notified' WHERE id IN ({$marks});";

        return $this->db()->executeUpdate($sql, $ids);
    }

    public function getSyncListByCursor($cursorAddress, $cursorType)
    {
        $sql = "SELECT id,data FROM {$this->table} where status = 'new' AND id > ? AND type = ?";

        return $this->db()->fetchAll($sql, [$cursorAddress, $cursorType]) ?: [];
    }

    public function findSyncListByCursor($cursorAddress, $cursorType)
    {
        $sql = "SELECT id,data FROM {$this->table} where id > ? AND type = ?";

        return $this->db()->fetchAll($sql, [$cursorAddress, $cursorType]) ?: [];
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time', 'updated_time'],
            'orderbys' => ['id', 'created_time', 'updated_time'],
            'conditions' => [],
        ];
    }
}
