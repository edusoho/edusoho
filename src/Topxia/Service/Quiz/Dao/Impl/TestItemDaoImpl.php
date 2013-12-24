<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Quiz\Dao\TestItemDao;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Connection;

class TestItemDaoImpl extends BaseDao implements TestItemDao
{
    protected $table = 'test_item';

    public function getItem($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addItem($item)
    {
        $item = $this->getConnection()->insert($this->table, $item);
        if ($item <= 0) {
            throw $this->createDaoException('Insert item error.');
        }
        return $this->getItem($this->getConnection()->lastInsertId());
    }

    public function addItems(array $items)
    {
        if(empty($items)){ 
            return array(); 
        }
        $items = implode(',',$items);
        $sql ="INSERT INTO {$this->table} (`testId`,`seq`,`questionId`,`questionType`,`parentId`,`score`) VALUES  {$items} ";
        return $this->getConnection()->executeUpdate($sql);
    }

    public function updateItem($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getItem($id);
    }

    public function deleteItem($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    } 

    public function deleteItemsByParentId($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE parentId = ?";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function findItemByIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findItemsByTestPaperId($testPaperId)
    {
        $sql ="SELECT * FROM {$this->table} WHERE testId = ? order by `seq` asc;";
        return $this->getConnection()->fetchAll($sql, array($testPaperId));
    }

    public function findItemsByTestPaperIdAndQuestionType($testPaperId, $field)
    {
        if(empty($testPaperId) || empty($field)){ 
            return array(); 
        }
        $sql ="SELECT * FROM {$this->table} WHERE `testId` = ? and `{$field[0]}` = '{$field[1]}'";
        return $this->getConnection()->fetchAll($sql, array($testPaperId));
    }
    
    public function getItemsCountByTestId($testId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE testId = ? ";
        return $this->getConnection()->fetchColumn($sql, array($testId));
    }

    public function deleteItemByIds(array $ids)
    {
        if(empty($ids)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }


}