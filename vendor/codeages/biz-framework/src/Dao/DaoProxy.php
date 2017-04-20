<?php

namespace Codeages\Biz\Framework\Dao;

use Pimple\Container;

class DaoProxy
{
    /**
     * @var DaoInterface
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

    public function __construct($container, DaoInterface $dao, SerializerInterface $serializer)
    {
        $this->container = $container;
        $this->dao = $dao;
        $this->serializer = $serializer;
        $this->cacheStrategy = false;
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
        foreach (array('get', 'find', 'search', 'count', 'create', 'update', 'wave', 'delete') as $prefix) {
            if (strpos($method, $prefix) === 0) {
                return $prefix;
            }
        }

        return null;
    }

    protected function get($method, $arguments)
    {
        $lastArgument = end($arguments);
        reset($arguments);

        if (is_array($lastArgument) && isset($lastArgument['lock']) && $lastArgument['lock'] === true) {
            $row = $this->callRealDao($method, $arguments);
            $this->unserialize($row);

            return $row;
        }

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $cache = $strategy->beforeGet($this->dao, $method, $arguments);
            if ($cache !== false) {
                return $cache;
            }
        }

        $row = $this->callRealDao($method, $arguments);
        $this->unserialize($row);

        if ($strategy) {
            $strategy->afterGet($this->dao, $method, $arguments, $row);
        }

        return $row;
    }

    protected function find($method, $arguments)
    {
        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $cache = $strategy->beforeFind($this->dao, $method, $arguments);
            if ($cache !== false) {
                return $cache;
            }
        }

        $rows = $this->callRealDao($method, $arguments);
        $this->unserializes($rows);

        if ($strategy) {
            $strategy->afterFind($this->dao, $method, $arguments, $rows);
        }

        return $rows;
    }

    protected function search($method, $arguments)
    {
        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $cache = $strategy->beforeSearch($this->dao, $method, $arguments);
            if ($cache !== false) {
                return $cache;
            }
        }

        $rows = $this->callRealDao($method, $arguments);
        $this->unserializes($rows);

        if ($strategy) {
            $strategy->afterSearch($this->dao, $method, $arguments, $rows);
        }

        return $rows;
    }

    protected function count($method, $arguments)
    {
        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $cache = $strategy->beforeCount($this->dao, $method, $arguments);
            if ($cache !== false) {
                return $cache;
            }
        }

        $count = $this->callRealDao($method, $arguments);

        if ($strategy) {
            $strategy->afterCount($this->dao, $method, $arguments, $count);
        }

        return $count;
    }

    protected function create($method, $arguments)
    {
        $declares = $this->dao->declares();

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

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->afterCreate($this->dao, $method, $arguments, $row);
        }

        return $row;
    }

    protected function wave($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);

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

        $strategy = $this->buildCacheStrategy();
        if ($strategy) {
            $this->buildCacheStrategy()->afterUpdate($this->dao, $method, $arguments, $row);
        }

        return $row;
    }

    protected function delete($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);

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

    private function buildCacheStrategy()
    {
        if ($this->cacheStrategy !== false) {
            return $this->cacheStrategy;
        }

        $firstEnabled = empty($this->container['dao.cache.first.enabled']) ? false : true;
        $secondEnabled = empty($this->container['dao.cache.second.enabled']) ? false : true;

        if ($secondEnabled) {
            $declares = $this->dao->declares();
            if (isset($declares['cache']) && $declares['cache'] === false) {
                $secondEnabled = false;
            } else {
                $secondStrategy = $this->container['dao.cache.second.strategy.'.(empty($declares['cache']) ? 'default' : $declares['cache'])];
            }
        }

        if ($firstEnabled && $secondEnabled) {
            $chain = $this->container['dao.cache.chain'];
            $chain->setStrategies($this->container['dao.cache.first'], $secondStrategy);

            return $this->cacheStrategy = $chain;
        }

        if ($firstEnabled && !$secondEnabled) {
            return  $this->cacheStrategy = $this->container['dao.cache.first'];
        }

        if (!$firstEnabled && $secondEnabled) {
            return  $this->cacheStrategy = $secondStrategy;
        }

        return null;
    }
}
