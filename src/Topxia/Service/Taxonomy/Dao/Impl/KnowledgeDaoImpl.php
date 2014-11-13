<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\KnowledgeDao;

class KnowledgeDaoImpl extends BaseDao implements KnowledgeDao 
{

    protected $table = 'knowledge';

    public function getKnowledge($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }
    
    public function updateKnowledge($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getKnowledge($id);
    }   

    public function deleteKnowledge($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }
    
    public function createKnowledge($knowledge)
    {
        $affected = $this->getConnection()->insert($this->table, $knowledge);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert knowledge error.');
        }
        return $this->getKnowledge($this->getConnection()->lastInsertId());
    }

    public function findKnowledgeByCategoryId($categoryId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE categoryId = ? ORDER BY weight ASC";
        return $this->getConnection()->fetchAll($sql, array($categoryId)) ? : array();
    }

    public function findKnowledgeByCategoryIdAndParentId($categoryId, $parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE categoryId = ? AND parentId = ? ORDER BY weight ASC";
        return $this->getConnection()->fetchAll($sql, array($categoryId, $parentId)) ? : array();
    }

    public function findKnowledgeByCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($code));
    }
}