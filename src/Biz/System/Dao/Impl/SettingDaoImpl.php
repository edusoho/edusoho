<?php

namespace Biz\System\Dao\Impl;

use Biz\System\Dao\SettingDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SettingDaoImpl extends GeneralDaoImpl implements SettingDao
{
    protected $table = 'setting';

    public function getByName($name)
    {
        return $this->getByFields(array('name' => $name));
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table}";

        return $this->db()->fetchAll($sql, array());
    }

    public function deleteByName($name)
    {
        return $this->db()->delete($this->table, array('name' => $name));
    }

    public function deleteByNamespaceAndName($namespace, $name)
    {
        return $this->db()->delete($this->table, array('namespace' => $namespace, 'name' => $name));
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'name = :name',
                'namespace = :namespace',
            ),
        );
    }
}
