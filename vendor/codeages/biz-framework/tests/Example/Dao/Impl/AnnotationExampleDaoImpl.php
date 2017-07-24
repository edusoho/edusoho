<?php

namespace Tests\Example\Dao\Impl;

use Codeages\Biz\Framework\Dao\Annotation\CacheStrategy;
use Codeages\Biz\Framework\Dao\Annotation\RowCache;

/**
 * @CacheStrategy("Row")
 */
class AnnotationExampleDaoImpl extends ExampleDaoImpl
{
    /**
     * @RowCache
     */
    public function getByName($name)
    {
        return $this->getByFields(array('name' => $name));
    }

    /**
     * @RowCache
     */
    public function getByCode($code)
    {
        return $this->getByFields(array('code' => $code));
    }

    public function findByName($name, $start, $limit)
    {
        return $this->search(array('name' => $name), array('created' => 'DESC'), $start, $limit);
    }

    public function findByNameAndId($name, $ids1)
    {
        return $this->findByFields(array('name' => $name, 'ids1' => $ids1));
    }

    public function findByIds(array $ids, array $orderBys, $start, $limit)
    {
        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "SELECT * FROM {$this->table()} WHERE id IN ({$marks})";

        return $this->db()->fetchAll($this->sql($sql, $orderBys, $start, $limit), $ids) ?: array();
    }

    public function updateByNameAndCode($name, $code, array $fields)
    {
        return $this->update(array('name' => $name, 'code' => $code), $fields);
    }
}
