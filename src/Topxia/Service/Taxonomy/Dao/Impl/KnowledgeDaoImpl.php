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

    public function searchKnowledges($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function findKnowledgeByCategoryId($categoryId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE categoryId = ? ORDER BY sequence ASC";
        return $this->getConnection()->fetchAll($sql, array($categoryId)) ? : array();
    }

    public function findKnowledgeByParentId($parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE parentId = ? ORDER BY sequence ASC";
        return $this->getConnection()->fetchAll($sql, array($parentId)) ? : array();
    }

    public function findKnowledgeByCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($code));
    }

    private function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'knowledge')
            ->andWhere('parentId = :parentId')
            ->andWhere('subjectId = :subjectId')
            ->andWhere('materialId = :materialId')
            ->andWhere('gradeId = :gradeId')
            ->andWhere('term =:term');

        return $builder;
    }
}