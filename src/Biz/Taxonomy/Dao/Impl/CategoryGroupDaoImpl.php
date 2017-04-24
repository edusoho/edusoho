<?php

namespace Biz\Taxonomy\Dao\Impl;

use Biz\Taxonomy\Dao\CategoryGroupDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CategoryGroupDaoImpl extends GeneralDaoImpl implements CategoryGroupDao
{
    protected $table = 'category_group';

    public function getByCode($code)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE code = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($code));
    }

    public function find($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table()} LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array()) ?: array();
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table()}";

        return $this->db()->fetchAll($sql) ?: array();
    }

    public function declares()
    {
        return array(
        );
    }

    protected function filterStartLimit(&$start, &$limit)
    {
        $start = (int) $start;
        $limit = (int) $limit;
    }
}
