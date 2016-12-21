<?php

namespace Codeages\Biz\Framework\Dao;

use Codeages\Biz\Framework\Context\Biz;

abstract class GeneralDaoImpl implements GeneralDaoInterface
{
    protected $biz;

    protected $table = null;

    protected $timestamps = array();

    protected $serializes = array();

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function create($fields)
    {
        $timestampField = $this->_getTimestampField('created');
        if ($timestampField) {
            $fields[$timestampField] = time();
        }

        $timestampField = $this->_getTimestampField('updated');
        if ($timestampField) {
            $fields[$timestampField] = time();
        }

        $affected = $this->db()->insert($this->table(), $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert error.');
        }

        return $this->get($this->db()->lastInsertId());
    }

    public function update($id, array $fields)
    {
        $timestampField = $this->_getTimestampField('updated');
        if ($timestampField) {
            $fields[$timestampField] = time();
        }

        $this->db()->update($this->table, $fields, array('id' => $id));

        return $this->get($id);
    }

    public function delete($id)
    {
        return $this->db()->delete($this->table(), array('id' => $id));
    }

    public function wave(array $ids, array $diffs)
    {
        $sets = array_map(function ($name) {
            return "{$name} = {$name} + ?";
        }, array_keys($diffs));

        $marks = str_repeat('?,', count($ids) - 1).'?';

        $sql = "UPDATE {$this->table()} SET ".implode(', ', $sets)." WHERE id IN ($marks)";

        return $this->db()->executeUpdate($sql, array_merge(array_values($diffs), $ids));
    }

    public function get($id, $lock = false)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE id = ?".($lock ? ' FOR UPDATE' : '');

        return $this->db()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function search($conditions, $orderbys, $start, $limit)
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $declares = $this->declares();
        foreach ($orderbys ?: array() as $field => $direction) {
            if (!in_array($field, $declares['orderbys'])) {
                throw $this->createDaoException(sprintf("SQL order by field is only allowed '%s', but you give `{$field}`.", implode(',', $declares['orderbys'])));
            }
            if (!in_array(strtoupper($direction), array('ASC', 'DESC'))) {
                throw $this->createDaoException("SQL order by direction is only allowed `ASC`, `DESC`, but you give `{$direction}`.");
            }
            $builder->addOrderBy($field, $direction);
        }

        return $builder->execute()->fetchAll();
    }

    public function count($conditions)
    {
        $builder = $this->_createQueryBuilder($conditions)
            ->select('COUNT(*)');

        return $builder->execute()->fetchColumn(0);
    }

    public function table()
    {
        return $this->table;
    }

    public function db()
    {
        return $this->biz['db'];
    }

    protected function getByFields($fields)
    {
        $placeholders = array_map(function ($name) {
            return "{$name} = ?";
        }, array_keys($fields));

        $sql = "SELECT * FROM {$this->table()} WHERE ".implode(' AND ', $placeholders);

        return $this->db()->fetchAssoc($sql, array_values($fields)) ?: null;
    }

    protected function findInField($field, $values)
    {
        if (empty($values)) {
            return array();
        }

        $marks = str_repeat('?,', count($values) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE {$field} IN ({$marks});";

        return $this->db()->fetchAll($sql, $values);
    }

    protected function findByFields($fields)
    {
        $placeholders = array_map(function ($name) {
            return "{$name} = ?";
        }, array_keys($fields));

        $sql = "SELECT * FROM {$this->table()} WHERE ".implode(' AND ', $placeholders);

        return $this->db()->fetchAll($sql, array_values($fields));
    }

    protected function _createQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value === '' || $value === null) {
                return false;
            }

            return true;
        });

        $builder = $this->_getQueryBuilder($conditions);
        $builder->from($this->table(), $this->table());

        $declares               = $this->declares();
        $declares['conditions'] = isset($declares['conditions']) ? $declares['conditions'] : array();

        foreach ($declares['conditions'] as $condition) {
            $builder->andWhere($condition);
        }

        return $builder;
    }

    protected function _getQueryBuilder($conditions)
    {
        return new DynamicQueryBuilder($this->db(), $conditions);
    }

    private function _getTimestampField($mode = null)
    {
        if (empty($this->timestamps)) {
            return;
        }

        if ($mode == 'created') {
            return isset($this->timestamps[0]) ? $this->timestamps[0] : null;
        } elseif ($mode == 'updated') {
            return isset($this->timestamps[1]) ? $this->timestamps[1] : null;
        } else {
            throw $this->createDaoException('mode error.');
        }
    }

    private function createDaoException($message = '', $code = 0)
    {
        return new DaoException($message, $code);
    }
}
