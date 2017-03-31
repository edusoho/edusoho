<?php
namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * 表级别缓存策略
 */
class TableCacheStrategy extends AbstractCacheStrategy implements CacheStrategy
{
    private $redis;

    private $logger;

    const LIFE_TIME = 3600;

    public function __construct($redis, $logger)
    {
        $this->redis = $redis;
        $this->logger = $logger;
    }

    public function beforeGet(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = $this->key($dao, $method, $arguments);
        return $this->redis->get($key);
    }

    public function afterGet(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $key = $this->key($dao, $method, $arguments);
        return $this->redis->set($key, $row, self::LIFE_TIME);
    }

    public function beforeFind(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = $this->key($dao, $method, $arguments);
        return $this->redis->get($key);
    }

    public function afterFind(GeneralDaoInterface $dao, $method, $arguments, array $rows)
    {
        $key = $this->key($dao, $method, $arguments);
        return $this->redis->set($key, $rows, self::LIFE_TIME);
    }

    public function beforeSearch(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = $this->key($dao, $method, $arguments);
        return $this->redis->get($key);
    }

    public function afterSearch(GeneralDaoInterface $dao, $method, $arguments, array $rows)
    {
        $key = $this->key($dao, $method, $arguments);
        return $this->redis->set($key, $rows, self::LIFE_TIME);
    }

    public function beforeCount(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = $this->key($dao, $method, $arguments);
        return $this->redis->get($key);
    }

    public function afterCount(GeneralDaoInterface $dao, $method, $arguments, $count)
    {
        $key = $this->key($dao, $method, $arguments);
        return $this->redis->set($key, $count, self::LIFE_TIME);
    }

    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->upTableVersion($dao);
    }

    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->upTableVersion($dao);
    }

    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected)
    {
        $this->upTableVersion($dao);
    }

    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments)
    {
        $this->upTableVersion($dao);
    }

    private function getTableVersion($dao)
    {
        $key = sprintf('dao:%s:v', $dao->table());
        $version = $this->redis->get($key);
        if ($version === false) {
            return $this->redis->incr($key);
        }

        return $version;
    }

    private function upTableVersion($dao)
    {
        $key = sprintf('dao:%s:v', $dao->table());
        return $this->redis->incr($key);
    }

    private function key(GeneralDaoInterface $dao, $method, $arguments)
    {
        $version = $this->getTableVersion($dao);
        $key = sprintf("dao:%s:v:%s:%s:%s", $dao->table(), $version, $method, json_encode($arguments));

        return $key;
    }
}