<?php

namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\BlockDao;

class BlockDaoImpl extends BaseDao implements BlockDao
{
    protected $table = 'block';

    public function getBlock($id)
    {
        return $this->fetch($id);
    }

    public function searchBlockCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return $this->getConnection()->fetchColumn($sql, array());
    }

    public function addBlock($block)
    {
        $id = $this->insert($block);
        return $this->getBlock($id);
    }

    public function deleteBlock($id)
    {
        return $this->delete($id);
    }

    public function getBlockByCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ?  LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($code));
    }

    public function findBlocks($start, $limit)
    {
        $sql = "SELECT * FROM {$this->table}  ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    public function updateBlock($id, array $fields)
    {
        return $this->update($id,$fields);
    }

}