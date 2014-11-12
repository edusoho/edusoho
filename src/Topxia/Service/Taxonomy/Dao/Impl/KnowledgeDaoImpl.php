<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\KnowledgeDao;

class KnowledgeDaoImpl extends BaseDao implements KnowledgeDao 
{

    protected $table = 'knowledge';

    public function addKnowledge($knowledge)
    {
        $affected = $this->getConnection()->insert($this->table, $knowledge);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert knowledge error.');
        }
        return $this->getCategory($this->getConnection()->lastInsertId());
    }

    public function findKnowledgeByCategoryId($categoryId)
    {
    	$sql = "SELECT * FROM {$this->table} WHERE categoryId = ? ORDER BY weight ASC";
    	return $this->getConnection()->fetchAll($sql, array($categoryId)) ? : array();
    }
}