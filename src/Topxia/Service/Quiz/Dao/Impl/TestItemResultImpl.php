<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Quiz\Dao\TestItemResultDaoImpl;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Connection;

class TestItemResultDaoImpl extends BaseDao implements TestItemResultDaoImpl
{
    protected $table = 'test_item_result';

    public function getResult($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getResultsByQuesitonId($questionId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE questionId = ? ";
        return $this->getConnection()->fetchAll($sql, array($questionId));
    }

    public function addResult($choice)
    {
        $choice = $this->getConnection()->insert($this->table, $choice);
        if ($choice <= 0) {
            throw $this->createDaoException('Insert choice error.');
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

    public function findResultsByQuestionIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE questionId IN ({$marks})";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function deleteResultsByQuestionIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE questionId IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }


}