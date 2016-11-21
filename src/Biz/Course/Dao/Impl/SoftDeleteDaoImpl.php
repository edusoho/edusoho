<?php

namespace Biz\Course\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

abstract class SoftDeleteDaoImpl extends GeneralDaoImpl
{
    public function get($id, $lock = false)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE id = ? and deleted=0 ".($lock ? ' FOR UPDATE' : '');
        return $this->db()->fetchAssoc($sql, array($id)) ?: array();
    }

    protected function getByFields($fields)
    {
        $placeholders = array_map(function ($name) {
            return "{$name} = ?";
        }, array_keys($fields));

        $sql = "SELECT * FROM {$this->table()} WHERE deleted=0 AND ".implode(' AND ', $placeholders);

        return $this->db()->fetchAssoc($sql, array_values($fields)) ?: null;
    }

    protected function findInField($field, $values)
    {
        if (empty($values)) {
            return array();
        }

        $marks = str_repeat('?,', count($values) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE deleted=0 AND {$field} IN ({$marks});";

        return $this->db()->fetchAll($sql, $values);
    }

    public function count($conditions)
    {
        $builder = $this->_createQueryBuilder($this->wrapConditions($conditions))
            ->select('COUNT(*)');

        return $builder->execute()->fetchColumn(0);
    }

    public function search($conditions, $orderbys, $start, $limit)
    {
        return parent::search($this->wrapConditions($conditions), $orderbys, $start, $limit);
    }

    public function wave(array $ids, array $diffs)
    {
        $sets = array_map(function ($name) {
            return "{$name} = {$name} + ?";
        }, array_keys($diffs));

        $marks = str_repeat('?,', count($ids) - 1).'?';

        $sql = "UPDATE {$this->table()} SET ".implode(', ', $sets)." WHERE deleted=0 AND id IN ($marks)";

        return $this->db()->executeUpdate($sql, array_merge(array_values($diffs), $ids));
    }

    public function delete($id)
    {
        $record = $this->get($id);
        if (!empty($record)) {
            $record['deleted'] = time();
        }
        $this->db()->update($this->table, $record, array('id' => $id));
        return 1; // effected row count
    }

    protected function wrapConditions($conditions)
    {
        if (empty($conditions)) {
            $conditions = array();
        }
        $conditions['deleted'] = 0;

        return $conditions;
    }
}
