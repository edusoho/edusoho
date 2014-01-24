<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Quiz\Dao\TestPaperDao;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Connection;

class TestPaperDaoImpl extends BaseDao implements TestPaperDao
{
    protected $table = 'testpaper';

    public function getTestPaper($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addTestPaper($testPaper)
    {
        $testPaper = $this->getConnection()->insert($this->table, $testPaper);
        if ($testPaper <= 0) {
            throw $this->createDaoException('Insert testPaper error.');
        }
        return $this->getTestPaper($this->getConnection()->lastInsertId());
    }

    public function updateTestPaper($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getTestPaper($id);
    }

    public function deleteTestPaper($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    } 

    public function deleteTestPapersByParentId($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE parentId = ?";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function findTestPaperByIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findTestPapersByTarget($targetType, $targetId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE targetType = ? AND targetId =? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($targetType, $targetId)) ? : array();
    }

    public function findTestPaperByTargetIdsAndTargetType(array $targetIds, $targetType)
    {
        if(empty($targetIds)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($targetIds) - 1) . '?';
        array_push($targetIds, $targetType);
        $sql ="SELECT * FROM {$this->table} WHERE targetId IN ({$marks}) AND targetType = ?;";
        return $this->getConnection()->fetchAll($sql, $targetIds) ? : array();
    }

    public function deleteTestPaperByIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }

    public function searchTestPaperCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
             ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchTestPaper($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1]);

        return $builder->execute()->fetchAll() ? : array();
    }

    private function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'questions')
            ->andWhere('targetId = :targetId')
            ->andWhere('targetType = :targetType');

        if (!empty($conditions['target'])) {
            $target = array();
            foreach ($conditions['target'] as $targetType => $targetIds) {
                foreach ($targetIds as $key => $targetId) {
                    $targetIds[] = (int) $targetId;
                }
                
                $target[] = " targetType ='".$targetType."' and targetId in (".join(' , ', $targetIds).")";
            }
            if (!empty($target)) {
                $target = join(' or ', $target);
                $builder->andStaticWhere(" ($target) ");
            }
        }

        return $builder;
    }

}