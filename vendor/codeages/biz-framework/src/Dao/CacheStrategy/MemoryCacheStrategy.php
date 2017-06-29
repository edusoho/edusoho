<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * 内存缓存策略.
 */
class MemoryCacheStrategy extends AbstractCacheStrategy implements CacheStrategy
{
    protected $cache = array();

    public function beforeGet(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = $this->key($dao, $method, $arguments);
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        return false;
    }

    public function afterGet(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $key = $this->key($dao, $method, $arguments);
        $this->cache[$key] = $row;
    }

    public function beforeFind(GeneralDaoInterface $dao, $method, $arguments)
    {
        return false;
    }

    public function afterFind(GeneralDaoInterface $dao, $method, $arguments, array $rows)
    {
    }

    public function beforeSearch(GeneralDaoInterface $dao, $method, $arguments)
    {
        return false;
    }

    public function afterSearch(GeneralDaoInterface $dao, $method, $arguments, array $rows)
    {
    }

    public function beforeCount(GeneralDaoInterface $dao, $method, $arguments)
    {
        return false;
    }

    public function afterCount(GeneralDaoInterface $dao, $method, $arguments, $count)
    {
    }

    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->cache = array();
    }

    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->cache = array();
    }

    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected)
    {
        $this->cache = array();
    }

    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments)
    {
        $this->cache = array();
    }

    private function key(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = sprintf('dao:%s:%s:%s', $dao->table(), $method, json_encode($arguments));

        return $key;
    }
}
