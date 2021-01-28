<?php

namespace Biz\Content\Dao\Impl;

use Biz\Content\Dao\BlockHistoryDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class BlockHistoryDaoImpl extends GeneralDaoImpl implements BlockHistoryDao
{
    protected $table = 'block_history';

    public function declares()
    {
        return array(
            'serializes' => array(
                'data' => 'json',
            ),
            'conditions' => array(
                'blockId =:blockId',
            ),
            'orderbys' => array(
                'createdTime',
            ),
        );
    }

    public function getLatest()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql) ?: null;
    }

    public function getLatestByBlockId($blockId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE blockId = ? ORDER BY createdTime DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($blockId)) ?: null;
    }

    public function deleteByBlockId($blockId)
    {
        return $this->db()->delete($this->table, array('blockId' => $blockId));
    }

    public function findByBlockId($blockId, $start, $limit)
    {
        return $this->search(
            array(
                'blockId' => $blockId,
            ),
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );
    }

    public function countByBlockId($blockId)
    {
        return $this->count(array(
            'blockId' => $blockId,
        ));
    }
}
