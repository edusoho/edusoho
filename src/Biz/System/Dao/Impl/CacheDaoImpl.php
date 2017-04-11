<?php

namespace Biz\System\Dao\Impl;

use Biz\System\Dao\CacheDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CacheDaoImpl extends GeneralDaoImpl implements CacheDao
{
    protected $table = 'cache';

    public function getByName($name)
    {
        return $this->getByFields(array('name' => $name));
    }

    public function findByNames(array $names)
    {
        if (empty($names)) {
            return array();
        }

        return $this->findInField('name', $names);
    }

    public function updateByName($name, $fields)
    {
        $cache = $this->getByName($name);

        return $this->update($cache['id'], $fields);
    }

    public function deleteByName($name)
    {
        return $this->db()->delete($this->table, array(
            'name' => $name,
        ));
    }

    public function deleteAll()
    {
        $sql = "DELETE FROM {$this->table}";
        $result = $this->db()->executeUpdate($sql, array());

        return $result;
    }

    public function declares()
    {
        return array(
        );
    }
}
