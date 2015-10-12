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

    public function searchBlockCount($condition)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if(isset($condition['category']) && !$this->isSortField($condition)){
              $sql .= " where category = '{$condition['category']}'";
        }
        return  $this->getConnection()->fetchColumn($sql, array());
    }


     protected function createBlockQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions,function($v){
            if($v === 0){
                return true;
            }
                
            if(empty($v)){
                return false;
            }
            return true;
        });
        if (isset($conditions['title'])) {
            $conditions['title'] = "%{$conditions['title']}%";
        }

        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'block')
            ->andWhere('category = :category')
            ->andWhere('title LIKE :title');
    }
    protected function isSortField($condition){
        if(isset($condition['category']) && $condition['category'] =='lastest'){
            return true;
        }
        return false;
    }

    public function addBlock($block)
    {
        $this->createSerializer()->serialize($block, $this->serializeFields);
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

    public function findBlocks($conditions, $orderBy,$start, $limit)
    {
        if(!isset($orderBy) || empty($orderBy)){
           $orderBy = array('createdTime','DESC');
        }
           $this->filterStartLimit($start, $limit);
           $builder = $this->createBlockQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
         $blocks = $builder->execute()->fetchAll() ? : array();
        return $blocks ? $this->createSerializer()->unserializes($blocks, $this->serializeFields) : array();
    }

    public function updateBlock($id, array $fields)
    {
        $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getBlock($id);
    }

}