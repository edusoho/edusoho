<?php

namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\BlockHistoryDao;

class BlockHistoryDaoImpl extends BaseDao implements BlockHistoryDao
{
    protected $table = 'block_history';

    private $serializeFields = array(
        'data' => 'json'
    );

    public function getBlockHistory($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $history = $this->getConnection()->fetchAssoc($sql, array($id));
        return $history ? $this->createSerializer()->unserialize($history, $this->serializeFields) : null;
    }

    public function getLatestBlockHistory()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql) ? : null;
    }

    public function getLatestBlockHistoryByBlockId($blockId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE blockId = ? ORDER BY createdTime DESC LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($blockId)) ? : null;
    }

    public function addBlockHistory($blockHistory)
    {
        $this->createSerializer()->serialize($blockHistory, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $blockHistory);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Block History error.');
        }
        return $this->getBlockHistory($this->getConnection()->lastInsertId());
    }

    public function deleteBlockHistory($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteBlockHistoryByBlockId($blockId)
    {
        return $this->getConnection()->delete($this->table, array('blockId' => $blockId));
    }
    
    public function findBlockHistorysByBlockId($blockId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE blockId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        $historys = $this->getConnection()->fetchAll($sql, array($blockId));
        return $historys ? $this->createSerializer()->unserializes($historys, $this->serializeFields) : array();
    }

    public function findBlockHistoryCountByBlockId($blockId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  blockId = ? ";
        return $this->getConnection()->fetchColumn($sql, array($blockId));
    }

}