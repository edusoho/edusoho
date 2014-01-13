<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Quiz\Dao\TestPaperResultDao;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Connection;

class TestPaperResultDaoImpl extends BaseDao implements TestPaperResultDao
{
    protected $table = 'test_paper_result';

    public function getResult($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addResult($questions)
    {
        $questions = $this->getConnection()->insert($this->table, $questions);
        if ($questions <= 0) {
            throw $this->createDaoException('Insert questions error.');
        }
        return $this->getResult($this->getConnection()->lastInsertId());
    }

    public function updateResult($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getResult($id);
    }

    public function deleteResult($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    } 

    public function deleteResultsByParentId($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE parentId = ?";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function searchResultCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
             ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchResult($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1]);

        return $builder->execute()->fetchAll() ? : array();
    }

    public function findResultByIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function deleteResultByIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }

    public function getResultByTestIdAndUserId ($testId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE testId = ? AND userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($testId, $userId)) ? : null;
    }

    public function findTestPaperResultsByUserId ($id, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE `userId` = ? ORDER BY beginTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($id)) ? : array();
    }

    public function findTestPaperResultsCountByUserId ($id)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `userId` = ?";
        return $this->getConnection()->fetchColumn($sql, array($id));
    }

    public function findWrongQuestionsByUserId ($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE `userId` = ? AND `status` IN ('wrong', 'noAnswer')";
        return $this->getConnection()->fetchAll($sql, array($id)) ? : array();
    }

    public function findTestPaperResultsByStatusAndTestIds ($ids, $status, $start, $limit)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';

        array_push($ids, $status);

        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE `testId` IN ({$marks}) AND `status` = ? ORDER BY endTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, $ids) ? : array();
    }

    public function findTestPaperResultCountByStatusAndTestIds ($ids, $status)
    {
        if(empty($ids)){ 
            return null; 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';

        array_push($ids, $status);

        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `testId` IN ({$marks}) AND `status` = ?";
        return $this->getConnection()->fetchColumn($sql, $ids);
    }

    private function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'questions')
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