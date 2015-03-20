<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\CategoryGroupDao;

class CategoryGroupDaoImpl extends BaseDao implements CategoryGroupDao
{
    protected $table = 'category_group';

    public function getGroup($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function findGroupByCode($code)
    {        
        $sql = "SELECT * FROM {$this->table} WHERE code = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($code));
    }

    public function findGroups($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array()) ? : array();
    }

    public function findAllGroups()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->getConnection()->fetchAll($sql) ? : array();
    }

    public function addGroup(array $group)
    {
        $affected = $this->getConnection()->insert($this->table, $group);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert group error.');
        }
        return $this->getGroup($this->getConnection()->lastInsertId());
    }

    public function deleteGroup($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }
}