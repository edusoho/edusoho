<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Quiz\Dao\TestPaperDao;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Connection;

class TestPaperDaoImpl extends BaseDao implements TestPaperDao
{
    protected $table = 'test_paper';

    public function getPaper($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addPaper($paper)
    {
        $paper = $this->getConnection()->insert($this->table, $paper);
        if ($paper <= 0) {
            throw $this->createDaoException('Insert paper error.');
        }
        return $this->getPaper($this->getConnection()->lastInsertId());
    }

    public function updatePaper($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getPaper($id);
    }

    public function deletePaper($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    } 

    public function deletePapersByParentId($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE parentId = ?";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function searchPaperCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
             ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchPaper($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1]);

        return $builder->execute()->fetchAll() ? : array();
    }

    public function findPaperByIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function deletePaperByIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }

    private function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'paper')
            ->andWhere('questionType = :questionType')
            ->andWhere('parentId = :parentId')
            ->andWhere('targetId = :targetId')
            ->andWhere('targetType = :targetType');

        if(empty($conditions['parentId'])){
            $builder->andStaticWhere(" `parentId` = '0' ");
            
        }   

        if (isset($conditions['target']) && empty($conditions['parentId']) ) {
            $target = array();
            foreach ($conditions['target'] as $targetType => $targetIds) {
                if (is_array($targetIds)) {
                    foreach ($targetIds as $key => $targetId) {
                        $targetIds[$key] = (int) $targetId;
                    }
                    $targetIds = join(' , ', $targetIds);
                } else {
                    $targetIds = (int) $targetIds;
                }
                $target[] = " targetType ='".$targetType."' and targetId in (".$targetIds.")"  ;
            }
            if (!empty($target)) {
                $target = join(' or ', $target);
                $builder->andStaticWhere(" ($target) ");
            }
        }



      
        return $builder;
    }

}