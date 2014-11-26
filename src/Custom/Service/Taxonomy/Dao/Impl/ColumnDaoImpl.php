<?php

namespace Custom\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Taxonomy\Dao\ColumnDao;

class ColumnDaoImpl extends BaseDao implements ColumnDao
{
    protected $table = 'special_column';

    public function getColumn($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function addColumn(array $column)
    {
        $affected = $this->getConnection()->insert($this->table, $column);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Column error.');
        }
        return $this->getColumn($this->getConnection()->lastInsertId());
    }

    public function updateColumn($id, array $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getColumn($id);
    }

    public function deleteColumn($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function findColumnsByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findColumnsByNames(array $names)
    {
        if(empty($names)){ return array(); }
        $marks = str_repeat('?,', count($names) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE name IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $names);
    }

    public function findAllColumns($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    public function getColumnByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($name));
    }

    public function getColumnByLikeName($name)
    {
        $name = "%{$name}%";
        $sql = "SELECT * FROM {$this->table} WHERE name LIKE ?";
        return $this->getConnection()->fetchAll($sql, array($name));
    }

    public function findAllColumnsCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} ";
        return $this->getConnection()->fetchColumn($sql, array());
    }

}