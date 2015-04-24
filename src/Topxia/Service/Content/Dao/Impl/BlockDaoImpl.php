<?php

namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\BlockDao;

class BlockDaoImpl extends BaseDao implements BlockDao
{
    protected $table = 'block';

    private $serializeFields = array(
        'meta' => 'json',
        'data' => 'json'
    );

    public function getBlock($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $block = $this->getConnection()->fetchAssoc($sql, array($id));
        return $block ? $this->createSerializer()->unserialize($block, $this->serializeFields) : null;
    }

    public function searchBlockCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return $this->getConnection()->fetchColumn($sql, array());
    }

    public function addBlock($block)
    {
        $affected = $this->getConnection()->insert($this->table, $block);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert block error.');
        }
        return $this->getBlock($this->getConnection()->lastInsertId());
    }

    public function deleteBlock($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function getBlockByCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ?  LIMIT 1";
        $block = $this->getConnection()->fetchAssoc($sql, array($code));
        return $block ? $this->createSerializer()->unserialize($block, $this->serializeFields) : null;
    }

    public function findBlocks($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table}  ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        $blocks = $this->getConnection()->fetchAll($sql, array());
        return $blocks ? $this->createSerializer()->unserializes($blocks, $this->serializeFields) : array();
    }

    public function updateBlock($id, array $fields)
    {
        $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getBlock($id);
    }

}