<?php

namespace MarketingMallBundle\Biz\SyncList\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use http\Env\Request;
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

        return $this->db()->fetchAll($sql);
    }

    public function getSyncDataId($id)
    {
        $sql = "SELECT id,type,status FROM {$this->table} where data =  {$id} ";

        return $this->db()->fetchAll($sql);
    }

    public function SyncStatusUpdate($ids)
    {

        $sql = "UPDATE {$this->table} set status = 'notified' where id in ({$ids})";
        return $this->db()->executeUpdate($sql);
    }

    public function getSyncListByCursor($cursorAddress, $cursorType)
    {
        $sql = "SELECT id,data FROM {$this->table} where status = 'new' AND id > ? AND type = ?";

        return $this->db()->fetchAll($sql, array($cursorAddress, $cursorType)) ?: array();
    }

    public function findSyncListByCursor($cursorAddress, $cursorType)
    {
        $sql = "SELECT id,data FROM {$this->table} where id > ? AND type = ?";

        return $this->db()->fetchAll($sql, array($cursorAddress, $cursorType)) ?: array();
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
