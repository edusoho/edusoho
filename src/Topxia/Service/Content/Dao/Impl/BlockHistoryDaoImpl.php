<?php

namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\BlockHistoryDao;

class BlockHistoryDaoImpl extends BaseDao implements BlockHistoryDao
{
    protected $table = 'block_history';

    public function getBlockHistory($id)
    {
        return $this->fetch($id);
    }

    public function addBlockHistory($blockHistory)
    {
        $id = $this->insert($blockHistory);
        return $this->getBlockHistory($id);
    }

    public function deleteBlockHistory($id)
    {
        return $this->delete($id);
    }

    public function deleteBlockHistoryByBlockId($blockId)
    {
        return $this->getConnection()->delete($this->table, array('blockId' => $blockId));
    }
    
    public function findBlockHistorysByBlockId($blockId, $start, $limit)
    {
        $sql = "SELECT * FROM {$this->table} WHERE blockId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($blockId));
    }

    public function findBlockHistoryCountByBlockId($blockId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  blockId = ? ";
        return $this->getConnection()->fetchColumn($sql, array($blockId));
    }

}