<?php

namespace Codeages\Biz\Framework\Dao;

trait SoftDelete
{
    protected $deleteFlagField = 'is_deleted';

    protected $deleteAtField = 'deleted_time';

    protected $includeDeleted = false;

    public function __call($name, $arguments)
    {
        $endsWith = 'IncludeDeleted';

        if (substr($name, -strlen($endsWith)) === $endsWith) {
            $name = method_exists($this, $name) ? $name : substr($name, 0, -strlen($endsWith));
            $this->includeDeleted = true;
        }
        if (method_exists($this, $name)) {
            $result = call_user_func_array([$this, $name], $arguments);
            $this->includeDeleted = false;

            return $result;
        }
        $this->includeDeleted = false;
        throw new DaoException("Method: {$name} not exists");
    }

    public function delete($id)
    {
        return $this->db()->update($this->table, [$this->deleteFlagField => 1, $this->deleteAtField => time()], ['id' => $id, $this->deleteFlagField => 0]);
    }

    public function wave(array $ids, array $diffs)
    {
        $sets = array_map(
            function ($name) {
                return "{$name} = {$name} + ?";
            },
            array_keys($diffs)
        );

        $marks = str_repeat('?,', count($ids) - 1) . '?';

        $sql = "UPDATE {$this->table()} SET " . implode(', ', $sets) . " WHERE id IN ({$marks}) AND {$this->deleteFlagField} = 0";

        return $this->db()->executeUpdate($sql, array_merge(array_values($diffs), $ids));
    }

    public function get($id, array $options = [])
    {
        $lock = isset($options['lock']) && true === $options['lock'];
        $sql = "SELECT * FROM {$this->table()} WHERE id = ?" . ($this->includeDeleted ? '' : " AND {$this->deleteFlagField} = 0") . ($lock ? ' FOR UPDATE' : '');

        return $this->db()->fetchAssoc($sql, [$id]) ?: null;
    }

    public function batchDelete(array $conditions)
    {
        return $this->updateByConditions($conditions, [$this->deleteFlagField => 1, $this->deleteAtField => time()]);
    }

    protected function updateById($id, $fields)
    {
        $this->db()->update($this->table, $fields, ['id' => $id, $this->deleteFlagField => 0]);

        return $this->get($id);
    }

    protected function getByFields($fields)
    {
        $placeholders = array_map(
            function ($name) {
                return "{$name} = ?";
            },
            array_keys($fields)
        );
        if (!$this->includeDeleted) {
            $placeholders[] = "{$this->deleteFlagField} = 0";
        }

        $sql = "SELECT * FROM {$this->table()} WHERE " . implode(' AND ', $placeholders) . ' LIMIT 1 ';

        return $this->db()->fetchAssoc($sql, array_values($fields)) ?: null;
    }

    protected function findInField($field, $values)
    {
        if (empty($values)) {
            return [];
        }

        $marks = str_repeat('?,', count($values) - 1) . '?';
        $sql = "SELECT * FROM {$this->table} WHERE {$field} IN ({$marks})" . ($this->includeDeleted ? '' : " AND {$this->deleteFlagField} = 0");

        return $this->db()->fetchAll($sql, $values);
    }

    protected function findByFields($fields)
    {
        $placeholders = array_map(
            function ($name) {
                return "{$name} = ?";
            },
            array_keys($fields)
        );
        if (!$this->includeDeleted) {
            $placeholders[] = "{$this->deleteFlagField} = 0";
        }

        $sql = "SELECT * FROM {$this->table()} WHERE " . implode(' AND ', $placeholders);

        return $this->db()->fetchAll($sql, array_values($fields));
    }

    protected function createQueryBuilder($conditions)
    {
        if (!$this->includeDeleted) {
            $conditions['isDeleted'] = 0;
        }
        $conditions = array_filter(
            $conditions,
            function ($value) {
                if ('' === $value || null === $value) {
                    return false;
                }

                if (is_array($value) && empty($value)) {
                    return false;
                }

                return true;
            }
        );

        $builder = $this->getQueryBuilder($conditions);
        $builder->from($this->table(), $this->table());

        $declares = $this->declares();
        $declares['conditions'] = isset($declares['conditions']) ? $declares['conditions'] : [];
        $declares['conditions'][] = "$this->deleteFlagField = :isDeleted";

        foreach ($declares['conditions'] as $condition) {
            $builder->andWhere($condition);
        }

        return $builder;
    }
}
