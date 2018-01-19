<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * 表级别缓存策略.
 */
class TableStrategy implements CacheStrategy
{
    private $redis;

    private $storage;

    const LIFE_TIME = 3600;

    const MAX_WAVE_CACHEABLE_TIMES = 32;

    public function __construct($redis, $storage)
    {
        $this->redis = $redis;
        $this->storage = $storage;
    }

    public function beforeQuery(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = $this->key($dao, $method, $arguments);

        return $this->redis->get($key);
    }

    public function afterQuery(GeneralDaoInterface $dao, $method, $arguments, $data)
    {
        $key = $this->key($dao, $method, $arguments);

        return $this->redis->set($key, $data, self::LIFE_TIME);
    }

    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->upTableVersion($dao);
    }

    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->upTableVersion($dao);
    }

    public function flush(GeneralDaoInterface $dao)
    {
        $this->upTableVersion($dao);
    }

    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected)
    {
        $declares = $dao->declares();
        if ('wave' === $method && !empty($declares['wave_cahceable_fields'])) {
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

                return;
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

        // 跑单元测试时，因为每个test会flushdb，而TableCacheStrategy又是单例，这里还缓存着原来的结果，会有问题，暂时注释，待重构
        if (isset($this->storage[$key])) {
            return $this->storage[$key];
        }

        $version = $this->redis->get($key);
        if (false === $version) {
            $version = $this->redis->incr($key);
        }

        $this->storage[$key] = $version;

        return $version;
    }

    private function upTableVersion($dao)
    {
        $key = sprintf('dao:%s:v', $dao->table());
        $version = $this->storage[$key] = $this->redis->incr($key);

        return $version;
    }

    private function key(GeneralDaoInterface $dao, $method, $arguments)
    {
        $version = $this->getTableVersion($dao);
        $key = sprintf('dao:%s:v:%s:%s:%s', $dao->table(), $version, $method, json_encode($arguments));

        return $key;
    }
}
