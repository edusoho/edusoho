<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Quiz\Dao\TestPaperDao;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Connection;

class TestPaperDaoImpl extends BaseDao implements TestPaperDao
{
    protected $table = 'test_paper';

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

    public function deleteTestPaperByIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }

}