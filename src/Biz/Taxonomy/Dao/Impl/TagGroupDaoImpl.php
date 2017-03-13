<?php

namespace Biz\Taxonomy\Dao\Impl;

use Biz\Taxonomy\Dao\TagGroupDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TagGroupDaoImpl extends GeneralDaoImpl implements TagGroupDao
{
    protected $table = 'tag_group';

    public function declares()
    {
        return array(
            'serializes' => array('scope' => 'delimiter'),
        );
    }

    public function getByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($name)) ?: null;
    }

    public function find()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC";

        return $this->db()->fetchAll($sql, array()) ?: array();
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }
}
