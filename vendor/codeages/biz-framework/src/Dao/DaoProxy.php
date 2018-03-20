<?php

namespace Codeages\Biz\Framework\Dao;

use Codeages\Biz\Framework\Dao\Annotation\MetadataReader;

class DaoProxy
{
    /**
     * @var GeneralDaoInterface
     */
    protected $dao;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var CacheStrategy
     */
    protected $cacheStrategy;

    /**
     * @var ArrayStorage
     */
    protected $arrayStorage;

    /**
     * @var MetadataReader
     */
    protected $metadataReader;

    public function __construct($container, DaoInterface $dao, MetadataReader $metadataReader, SerializerInterface $serializer, ArrayStorage $arrayStorage = null)
    {
        $this->container = $container;
        $this->dao = $dao;
        $this->metadataReader = $metadataReader;
        $this->serializer = $serializer;
        $this->arrayStorage = $arrayStorage;
    }

    public function __call($method, $arguments)
    {
        $proxyMethod = $this->getProxyMethod($method);
        if ($proxyMethod) {
            return $this->$proxyMethod($method, $arguments);
        } else {
            return $this->callRealDao($method, $arguments);
        }
    }

    protected function getProxyMethod($method)
    {
        foreach (array('get', 'find', 'search', 'count', 'create', 'batchCreate', 'batchUpdate', 'batchDelete', 'update', 'wave', 'delete') as $prefix) {
            if (0 === strpos($method, $prefix)) {
                return $prefix;
            }
        }

        return null;
    }

    /**
     * 代理 get 开头的方法调用
     *
     * @param string $method 被调用的 Dao 方法名
     * @param array $arguments 调用参数
     * @return array|null
     */
    protected function get($method, $arguments)
    {
        $lastArgument = end($arguments);
        reset($arguments);
        
        // lock模式下，因为需要借助mysql的锁，不走cache
        if (is_array($lastArgument) && isset($lastArgument['lock']) && true === $lastArgument['lock']) {
            $row = $this->callRealDao($method, $arguments);
            $this->unserialize($row);

            return $row;
        }

        if ($this->arrayStorage) {
            $key = $this->getCacheKey($this->dao, $method, $arguments);
            if (!empty($this->arrayStorage[$key])) {
                return $this->arrayStorage[$key];
            }
        }

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $cache = $strategy->beforeQuery($this->dao, $method, $arguments);
            // 命中 cache, 直接返回 cache 数据
            if (false !== $cache) {
                return $cache;
            }
        }

        $row = $this->callRealDao($method, $arguments);
        $this->unserialize($row);

        // 将结果缓存至 ArrayStorage
        $this->arrayStorage && ($this->arrayStorage[$this->getCacheKey($this->dao, $method, $arguments)] = $row);

        if ($strategy) {
            $strategy->afterQuery($this->dao, $method, $arguments, $row);
        }

        return $row;
    }

    protected function find($method, $arguments)
    {
        return $this->search($method, $arguments);
    }

    protected function search($method, $arguments)
    {
        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $cache = $strategy->beforeQuery($this->dao, $method, $arguments);
            if (false !== $cache) {
                return $cache;
            }
        }

        $rows = $this->callRealDao($method, $arguments);

        if (!empty($rows)) {
            $this->unserializes($rows);
        }

        if ($strategy) {
            $strategy->afterQuery($this->dao, $method, $arguments, $rows);
        }

        return $rows;
    }

    protected function count($method, $arguments)
    {
        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $cache = $strategy->beforeQuery($this->dao, $method, $arguments);
            if (false !== $cache) {
                return $cache;
            }
        }

        $count = $this->callRealDao($method, $arguments);

        if ($strategy) {
            $strategy->afterQuery($this->dao, $method, $arguments, $count);
        }

        return $count;
    }

    protected function create($method, $arguments)
    {
        $declares = $this->dao->declares();

        $generator = $this->getIdGenerator();
        if ($generator) {
            $id = $arguments[0]['id'] = $generator->generate();
        }

        $time = time();

        if (isset($declares['timestamps'][0])) {
            $arguments[0][$declares['timestamps'][0]] = $time;
        }

        if (isset($declares['timestamps'][1])) {
            $arguments[0][$declares['timestamps'][1]] = $time;
        }

        $this->serialize($arguments[0]);
        $row = $this->callRealDao($method, $arguments);
        $this->unserialize($row);

        $this->arrayStorage && $this->arrayStorage->flush();

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->afterCreate($this->dao, $method, $arguments, $row);
        }

        return $row;
    }

    protected function batchCreate($method, $arguments)
    {
        $declares = $this->dao->declares();

        end($arguments);
        $lastKey = key($arguments);
        reset($arguments);

        if (!is_array($arguments[$lastKey])) {
            throw new DaoException('batchCreate method arguments last element must be array type');
        }

        $time = time();
        $rows = $arguments[$lastKey];

        $generator = $this->getIdGenerator();

        foreach ($rows as &$row) {
            if ($generator) {
                $row['id'] = $generator->generate();
            }

            if (isset($declares['timestamps'][0])) {
                $row[$declares['timestamps'][0]] = $time;
            }

            if (isset($declares['timestamps'][1])) {
                $row[$declares['timestamps'][1]] = $time;
            }

            $this->serialize($row);
            unset($row);
        }

        $arguments[$lastKey] = $rows;

        $result = $this->callRealDao($method, $arguments);

        $this->flushTableCache();

        return $result;
    }

    protected function batchUpdate($method, $arguments)
    {
        $declares = $this->dao->declares();

        $time = time();
        $rows = $arguments[1];

        foreach ($rows as &$row) {
            if (isset($declares['timestamps'][1])) {
                $row[$declares['timestamps'][1]] = $time;
            }

            $this->serialize($row);
        }

        $arguments[1] = $rows;

        $result = $this->callRealDao($method, $arguments);

        $this->flushTableCache();

        return $result;
    }

    protected function batchDelete($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);

        $this->flushTableCache();

        return $result;
    }

    protected function wave($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);

        $this->arrayStorage && $this->arrayStorage->flush();

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->afterWave($this->dao, $method, $arguments, $result);
        }

        return $result;
    }

    protected function update($method, $arguments)
    {
        $declares = $this->dao->declares();

        end($arguments);
        $lastKey = key($arguments);
        reset($arguments);

        if (!is_array($arguments[$lastKey])) {
            throw new DaoException('update method arguments last element must be array type');
        }

        if (isset($declares['timestamps'][1])) {
            $arguments[$lastKey][$declares['timestamps'][1]] = time();
        }

        $this->serialize($arguments[$lastKey]);

        $row = $this->callRealDao($method, $arguments);

        if (is_array($row)) {
            $this->unserialize($row);
        }

        if (!is_array($row) && !is_numeric($row) && !is_null($row)) {
            throw new DaoException('update method return value must be array type or int type');
        }

        $this->arrayStorage && $this->arrayStorage->flush();

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->afterUpdate($this->dao, $method, $arguments, $row);
        }

        return $row;
    }

    protected function delete($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);

        $this->arrayStorage && $this->arrayStorage->flush();

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->afterDelete($this->dao, $method, $arguments);
        }

        return $result;
    }

    protected function callRealDao($method, $arguments)
    {
        return call_user_func_array(array($this->dao, $method), $arguments);
    }

    protected function unserialize(&$row)
    {
        if (empty($row)) {
            return;
        }

        $declares = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!array_key_exists($key, $row)) {
                continue;
            }

            $row[$key] = $this->serializer->unserialize($method, $row[$key]);
        }
    }

    protected function unserializes(array &$rows)
    {
        foreach ($rows as &$row) {
            $this->unserialize($row);
        }
    }

    protected function serialize(&$row)
    {
        $declares = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!array_key_exists($key, $row)) {
                continue;
            }

            $row[$key] = $this->serializer->serialize($method, $row[$key]);
        }
    }

    protected function getIdGenerator()
    {
        $declares = $this->declares();
        if (empty($declares['id_generator'])) {
            return null;
        }
        return $this->container['dao.id_generator.'.$declares['id_generator']];
    }

    private function flushTableCache()
    {
        $this->arrayStorage && ($this->arrayStorage->flush());

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->flush($this->dao);
        }
    }

    /**
     * @return CacheStrategy|null
     */
    private function buildCacheStrategy()
    {
        if (!empty($this->cacheStrategy)) {
            return $this->cacheStrategy;
        }

        if (empty($this->container['dao.cache.enabled'])) {
            return null;
        }

        if (!empty($this->container['dao.cache.annotation'])) {
            $strategy = $this->getCacheStrategyFromAnnotation($this->dao);
            if ($strategy) {
                return $strategy;
            }
        }

        $declares = $this->dao->declares();

        // 未指定 cache 策略，则使用默认策略
        if (!isset($declares['cache'])) {
            return $this->container['dao.cache.strategy.default'];
        }

        // 针对某个 Dao 关闭 Cache
        if (false === $declares['cache']) {
            return null;
        }

        // 针对某个 Dao 指定 Cache 策略
        $strategyServiceId = 'dao.cache.strategy.'.strtolower($declares['cache']);
        if (!isset($this->container[$strategyServiceId])) {
            throw new DaoException("Dao %s cache strategy is not defined, please define first in biz container use %s service id.", get_class($this->dao), $strategyServiceId);
        }

        return $this->container[$strategyServiceId];
    }

    private function getCacheStrategyFromAnnotation($dao)
    {
        $metadata = $this->metadataReader->read($dao);
        if (empty($metadata)) {
            return null;
        }

        $strategyServiceId = 'dao.cache.strategy.'.strtolower($metadata['strategy']);
        if (!isset($this->container[$strategyServiceId])) {
            throw new DaoException("Dao %s cache strategy is not defined, please define first in biz container use %s service id.", get_class($this->dao), $strategyServiceId);
        }

        return $this->container[$strategyServiceId];
    }

    private function getCacheKey(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = sprintf('dao:%s:%s:%s', $dao->table(), $method, json_encode($arguments));

        return $key;
    }
}
