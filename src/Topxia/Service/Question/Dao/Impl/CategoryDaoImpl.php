<?php
namespace Topxia\Service\Question\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Question\Dao\CategoryDao;

class CategoryDaoImpl extends BaseDao implements CategoryDao
{
    protected $table = 'question_category';

    public function getCategory($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findCategoriesByTarget($target, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE target = ? ORDER BY seq ASC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($target)) ? : array();
    }

    public function getCategorysCountByTarget($target)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE target = ?";
        return $this->getConnection()->fetchColumn($sql, array($target));
    }

    public function findCategoriesByIds($ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function addCategory($fields)
    {   
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert question category error.');
        }
        return $this->getCategory($this->getConnection()->lastInsertId());
    }

    public function updateCategory($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getCategory($id);
    }

    public function deleteCategory($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

}