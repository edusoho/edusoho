<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\Annotation\MetadataReader;
use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * 行级别缓存策略
 */
class RowStrategy implements CacheStrategy
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var MetadataReader
     */
    private $metadataReader;

    const LIFE_TIME = 3600;

    public function __construct($redis, MetadataReader $metadataReader)
    {
        $this->redis = $redis;
        $this->metadataReader = $metadataReader;
    }

    public function beforeQuery(GeneralDaoInterface $dao, $method, $arguments)
    {
        if (0 !== strpos($method, 'get')) {
            return false;
        }

        $metadata = $this->metadataReader->read($dao);

        $key = $this->getCacheKey($dao, $metadata, $method, $arguments);
        if (!$key) {
            return false;
        }

        $cache = $this->redis->get($key);
        if (false === $cache) {
            return false;
        }

        if ('get' === $method) {
            return $cache;
        }

        return $this->redis->get($cache);
    }

    public function afterQuery(GeneralDaoInterface $dao, $method, $arguments, $data)
    {
        if (empty($data)) {
            return;
        }
        if (0 !== strpos($method, 'get')) {
            return;
        }

        $metadata = $this->metadataReader->read($dao);

        $key = $this->getCacheKey($dao, $metadata, $method, $arguments);
        if (!$key) {
            return;
        }

        if ('get' === $method) {
            $this->redis->set($key, $data, self::LIFE_TIME);
        } else {
            $primaryKey = $this->getPrimaryCacheKey($dao, $metadata, $data['id']);
            $this->redis->set($primaryKey, $data, self::LIFE_TIME);
            $this->redis->set($key, $primaryKey, self::LIFE_TIME);
            $this->saveRelKeys($primaryKey, $key, self::LIFE_TIME);
        }
    }

    private function saveRelKeys($primaryKey, $key, $lifetime)
    {
        $existRelKeys = $this->getRelKeys($primaryKey);
        $existRelKeys = array_merge($existRelKeys, array($key));
        $this->redis->set($primaryKey.':rel_keys', $existRelKeys, $lifetime);
    }

    private function getRelKeys($primaryKey)
    {
        $keys = $this->redis->get($primaryKey.':rel_keys');
        if (empty($keys) || !is_array($keys)) {
            $keys = array();
        }

        return $keys;
    }

    private function delRelKeys($primaryKey)
    {
        $relKeys = $this->getRelKeys($primaryKey);
        foreach ($relKeys as $relKey) {
            $this->redis->del($relKey);
        }
        $this->redis->del($primaryKey.':rel_keys');
    }

    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        return;
    }

    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $metadata = $this->metadataReader->read($dao);
        $primaryKey = $this->getPrimaryCacheKey($dao, $metadata, $row['id']);
        $this->redis->del($primaryKey);
        $this->delRelKeys($primaryKey);
    }

    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments)
    {
        $metadata = $this->metadataReader->read($dao);
        // $arguments[0] is GeneralDaoInterface delete function first argument `id`.
        $primaryKey = $this->getPrimaryCacheKey($dao, $metadata, $arguments[0]);
        $this->redis->del($primaryKey);
        $this->delRelKeys($primaryKey);
    }

    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected)
    {
        $metadata = $this->metadataReader->read($dao);
        // $arguments[0] is GeneralDaoInterface wave function first argument `$ids`.
        foreach ($arguments[0] as $id) {
            $primaryKey = $this->getPrimaryCacheKey($dao, $metadata, $id);
            $this->redis->del($primaryKey);
            $this->delRelKeys($primaryKey);
        }
    }

    public function flush(GeneralDaoInterface $dao)
    {
        $keys = $this->redis->keys("dao:{$dao->table()}:*");
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }

    protected function getCacheKey(GeneralDaoInterface $dao, $metadata, $method, $arguments)
    {
        $argumentsForKey = array();

        if (empty($metadata['cache_key_of_arg_index'][$method])) {
            return false;
        }

        foreach ($metadata['cache_key_of_arg_index'][$method] as $index) {
            $argumentsForKey[] = $arguments[$index];
        }

        $key = "dao:{$dao->table()}:{$method}:";

        return $key.implode(',', $argumentsForKey);
    }

    protected function getPrimaryCacheKey(GeneralDaoInterface $dao, $metadata, $id)
    {
        return $this->getCacheKey($dao, $metadata, 'get', array($id));
    }
}
