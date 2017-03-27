<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

abstract class CacheStrategy
{
    protected $container;
    protected $maxLifeTime = 86400;

    abstract public function wave($dao, $method, $arguments, $callback);
    abstract protected function generateKey($dao, $method, $arguments);

    public function __construct($container)
    {
        $this->container = $container;
        $this->maxLifeTime = empty($container['cache.config']['maxLifeTime']) ? 86400: $container['cache.config']['maxLifeTime'];
    }

    public function parseDao($dao)
    {
    }

    protected function parseFields($method)
    {
        $prefixs = array('get', 'find');
        $prefix  = $this->getPrefix($method, $prefixs);

        if (empty($prefix)) {
            return array();
        }

        $method = str_replace($prefix.'By', '', $method);

        $fileds = explode("And", $method);
        foreach ($fileds as $key => $filed) {
            $fileds[$key] = lcfirst($filed);
        }

        return $fileds;
    }

    protected function getPrefix($str, $prefixs)
    {
        $_prefix = '';
        foreach ($prefixs as $prefix) {
            if (strpos($str, $prefix) === 0) {
                $_prefix = $prefix;
                break;
            }
        }

        return $_prefix;
    }

    public function set($dao, $method, $arguments, $data)
    {
        $prefix = $this->getPrefix($method, array('get', 'find'));

        if (!empty($prefix)) {
            $key = $this->generateKey($dao, $method, $arguments);
            $this->_getCacheCluster($dao->table())->setex($key, $this->maxLifeTime, $data);
        }
    }

    public function get($dao, $method, $arguments)
    {
        $prefix = $this->getPrefix($method, array('get', 'find'));

        if (!empty($prefix)) {
            $key = $this->generateKey($dao, $method, $arguments);
            return $this->_getCacheCluster($dao->table())->get($key);
        }
    }

    protected function incrNamespaceVersion($dao, $namespace)
    {
        $this->_getCacheCluster($dao->table())->incr("version:{$namespace}");
    }

    protected function getVersionByNamespace($dao, $namespace)
    {
        return $this->_getCacheCluster($dao->table())->get("version:{$namespace}");
    }

    protected function _getCacheCluster($group = 'default')
    {
        return $this->container['cache.cluster']->getCluster($group);
    }
}
