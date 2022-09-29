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

    public function updateSyncType()
    {
        $sql = "SELECT type FROM {$this->table} where status = 'new' group by type";

        return $this->db()->fetchAll($sql);
    }

    public function getSyncListByCursor($cursorAddress, $cursorType)
    {
        $sql = "SELECT id,data FROM {$this->table} where status = 'new' AND id > ? AND type = 'updateUser'";

        return $this->db()->fetchAll($sql, array($cursorAddress)) ?: array();
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array('id', 'created_time', 'updated_time'),
            'conditions' => array(),
        );
    }
}
