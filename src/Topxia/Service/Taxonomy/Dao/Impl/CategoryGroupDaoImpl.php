<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\CategoryGroupDao;

class CategoryGroupDaoImpl extends BaseDao implements CategoryGroupDao
{
    protected $table = 'category_group';

    public function getGroup($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id));
        }

        );
    }

    public function findGroupByCode($code)
    {
        $that = $this;

        return $this->fetchCached("code:{$code}", $code, function ($code) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE code = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($code));
        }

        );
    }

    public function findGroups($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array()) ?: array();
    }

    public function findAllGroups()
    {
        $that = $this;

        return $this->fetchCached("all", function () use ($that) {
            $sql = "SELECT * FROM {$that->getTable()}";
            return $that->getConnection()->fetchAll($sql) ?: array();
        }

        );
    }

    public function addGroup(array $group)
    {
        $affected = $this->getConnection()->insert($this->table, $group);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert group error.');
        }

        $this->clearCached();
        return $this->getGroup($this->getConnection()->lastInsertId());
    }

    public function deleteGroup($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }
}
