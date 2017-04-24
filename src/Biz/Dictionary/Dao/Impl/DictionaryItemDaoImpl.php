<?php

namespace Biz\Dictionary\Dao\Impl;

use Biz\Dictionary\Dao\DictionaryItemDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DictionaryItemDaoImpl extends GeneralDaoImpl implements DictionaryItemDao
{
    protected $table = 'dictionary_item';

    public function findAllOrderByWeight()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY weight DESC";

        return $this->db()->fetchAll($sql, array());
    }

    public function findByName($name)
    {
        return $this->findByFields(array('name' => $name));
    }

    public function findByType($type)
    {
        return $this->findByFields(array('type' => $type));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updateTime'),
            'orderbys' => array('weight'),
            'conditions' => array(
                'name = :name',
                'type = :type',
            ),
        );
    }
}
