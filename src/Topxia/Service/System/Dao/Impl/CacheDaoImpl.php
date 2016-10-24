<?php

namespace Topxia\Service\System\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\System\Dao\CacheDao;

class CacheDaoImpl extends BaseDao implements CacheDao
{
    protected $table = 'cache';

    public function getCache($id)
    {
        $redis = $this->getRedis();
        if ($redis) {
            $key  = "{$this->table}:v{$this->getTableVersion()}:id:{$id}";
            $data = empty($this->dataCached[$key]) ? '' : $this->dataCached[$key];
            if (empty($data)) {
                $data                   = $this->getRedis()->get($key);
                $this->dataCached[$key] = $data;
            }
            return $data;
        } else {
            $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
            return $this->getConnection()->fetchAssoc($sql, array($id));
        }
    }

    public function addCache($cache)
    {
        $redis = $this->getRedis();

        if ($redis) {
            $key = "{$this->table}:v{$this->getTableVersion()}:name:{$cache['name']}";
            $redis->setex($key, 2 * 60 * 60, $cache);
            $this->dataCached[$key] = $cache;
            return $cache;
        } else {
            $affected = $this->getConnection()->insert($this->table, $cache);

            if ($affected <= 0) {
                throw $this->createDaoException('Insert cache error.');
            }

            return $this->getCache($this->getConnection()->lastInsertId());
        }
    }

    protected function getCacheByName($name)
    {
        $key  = "{$this->table}:v{$this->getTableVersion()}:name:{$name}";
        $data = empty($this->dataCached[$key]) ? '' : $this->dataCached[$key];
        if (empty($data)) {
            $data                   = $this->getRedis()->get($key);
            $this->dataCached[$key] = $data;
        }
        return $data;
    }

    public function findCachesByNames(array $names)
    {
        if (empty($names)) {
            return array();
        }

        $redis = $this->getRedis();

        if ($redis) {
            $datas = array();
            foreach ($names as $key => $name) {
                $datas[] = $this->getCacheByName($name);
            }
            return $datas;
        } else {
            $marks = str_repeat('?,', count($names) - 1).'?';

            $sql = "SELECT * FROM {$this->getTable()} WHERE name IN ({$marks});";
            return $this->getConnection()->fetchAll($sql, $names);
        }
    }

    public function updateCache($name, $cache)
    {
        $redis = $this->getRedis();

        if ($redis) {
            $key = "{$this->table}:v{$this->getTableVersion()}:name:{$name}";
            $redis->setex($key, 2 * 60 * 60, $cache);
            $this->dataCached[$key] = $cache;
            return $cache;
        } else {
            $this->getConnection()->update($this->table, $cache, array('name' => $name));
            $caches = $this->findCachesByNames(array($name));
            return $caches[0];
        }
    }

    public function deleteCacheByName($name)
    {
        $redis = $this->getRedis();

        if ($redis) {
            $key = "{$this->table}:v{$this->getTableVersion()}:name:{$name}";
            unset($this->dataCached[$key]);
            return $redis->delete($key);
        } else {
            $result = $this->getConnection()->delete($this->table, array('name' => $name));
            return $result;
        }
    }

    public function deleteAllCache()
    {
        $redis = $this->getRedis();

        if ($redis) {
            return $this->clearCached();
        } else {
            $sql    = "DELETE FROM {$this->table}";
            $result = $this->getConnection()->executeUpdate($sql, array());
            return $result;
        }
    }
}
