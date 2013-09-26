<?php

namespace Topxia\Service\System\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\System\Dao\CacheDao;

class CacheDaoImpl extends BaseDao implements CacheDao
{
    protected $table = 'cache';

    public function getCache($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function addCache($cache)
    {
        $affected = $this->getConnection()->insert($this->table, $cache);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert cache error.');
        }
        return $this->getCache($this->getConnection()->lastInsertId());
    }

    public function findCachesByNames(array $names)
    {
        if(empty($names)){
            return array();
        }
        $marks = str_repeat('?,', count($names) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE name IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $names);
    }

    public function deleteCacheByName($name)
    {
        return $this->getConnection()->delete($this->table, array('name' => $name));
    }

    public function deleteAllCache()
    {
        $sql = "DELETE FROM {$this->table}";
        return $this->getConnection()->executeUpdate($sql, array());
    }
}