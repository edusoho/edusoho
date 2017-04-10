<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * 表级别缓存策略.
 */
class TableCacheStrategy extends AbstractCacheStrategy implements CacheStrategy
{
    private $redis;

    private $logger;

    private $versions;

    const LIFE_TIME = 3600;

    const MAX_WAVE_CACHEABLE_TIMES = 32;

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
        $declares = $dao->declares();
        if ($method === 'wave' && !empty($declares['wave_cahceable_fields'])) {
            $cacheable = true;
            foreach (array_keys($arguments[1]) as $key) {
                if (!in_array($key, $declares['wave_cahceable_fields'])) {
                    $cacheable = false;
                    break;
                }
            }
            if ($cacheable) {
                $key = sprintf('dao:%s:%s:%s:wave_times', $dao->table(), $method, json_encode($arguments));
                $waveTimes = $this->redis->incr($key);
                if ($waveTimes > self::MAX_WAVE_CACHEABLE_TIMES) {
                    $this->redis->delete($key);
                    goto end;
                } else {
                    foreach ($arguments[0] as $id) {
                        $cachKey = $this->key($dao, 'get', array($id));
                        $row = $this->redis->get($cachKey);
                        if ($row) {
                            foreach ($arguments[1] as $key => $value) {
                                $row[$key] += $value;
                                $row[$key] = (string) $row[$key];
                            }
                            $this->redis->set($cachKey, $row, self::LIFE_TIME);
                        }
                    }
                }
                return ;
            }
        }

        end:
        $this->upTableVersion($dao);
    }

    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments)
    {
        $this->upTableVersion($dao);
    }

    private function getTableVersion($dao)
    {
        $key = sprintf('dao:%s:v', $dao->table());

        if (isset($this->versions[$key])) {
            return $this->versions[$key];
        }

        $version = $this->redis->get($key);
        if ($version === false) {
            $version = $this->redis->incr($key);
        }

        $this->versions[$key] = $version;

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
        $key = sprintf('dao:%s:v:%s:%s:%s', $dao->table(), $version, $method, json_encode($arguments));

        return $key;
    }
}
