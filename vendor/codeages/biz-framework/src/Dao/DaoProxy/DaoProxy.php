<?php

namespace Codeages\Biz\Framework\Dao\DaoProxy;

use Codeages\Biz\Framework\Dao\DaoException;
use Codeages\Biz\Framework\Dao\FieldSerializer;

class DaoProxy
{
    protected $container;
    protected $dao;
    protected $cache = array();

    /**
     * @var FieldSerializer
     */
    protected $serializer;

    public function __construct($container)
    {
        $this->container = $container;
        $this->serializer = new FieldSerializer();
    }

    public function setDao($dao)
    {
        $this->dao = $dao;
    }

    public function __call($method, $arguments)
    {
        $daoProxyMethod = $this->getDaoProxyMethod($method);

        if ($daoProxyMethod) {
            return $this->$daoProxyMethod($method, $arguments);
        } else {
            return $this->callRealDao($method, $arguments);
        }
    }

    protected function clearMemoryCache()
    {
        $this->cache = array();
    }

    protected function getDaoProxyMethod($method)
    {
        $prefix = $this->getPrefix($method, array('get', 'find', 'create', 'update', 'delete', 'search', 'wave'));
        if ($prefix) {
            return "{$prefix}";
        }
    }

    protected function wave($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);
        $this->clearMemoryCache();
        return $result;
    }

    protected function getPrefix($method, $prefixs)
    {
        $_prefix = '';
        foreach ($prefixs as $prefix) {
            if (strpos($method, $prefix) === 0) {
                $_prefix = $prefix;
                break;
            }
        }

        return $_prefix;
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
        $this->clearMemoryCache();
        $this->unserialize($row);
        return $row;
    }

    protected function create($method, $arguments)
    {
        $declares = $this->dao->declares();
        if (isset($declares['timestamps'][0])) {
            $arguments[0][$declares['timestamps'][0]] = time();
        }

        if (isset($declares['timestamps'][1])) {
            $arguments[0][$declares['timestamps'][1]] = time();
        }

        $this->serialize($arguments[0]);
        $row          = $this->callRealDao($method, $arguments);
        $this->clearMemoryCache();
        $this->unserialize($row);
        return $row;
    }

    protected function delete($method, $arguments)
    {
        $result = $this->callRealDao($method, $arguments);
        $this->clearMemoryCache();
        return $result;
    }

    protected function get($method, $arguments)
    {
        $key = $this->getMemoryCacheKey($method, $arguments);
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $row = $this->callRealDao($method, $arguments);
        $this->unserialize($row);
        $this->cache[$key] = $row;
        return $row;
    }

    protected function getMemoryCacheKey($method, $arguments)
    {
        if (empty($arguments)) {
            return $method;
        }

        if (is_array($arguments)) {
            return $method.':'.json_encode($arguments);
        }

        return $method.':'.$arguments;
    }

    protected function find($method, $arguments)
    {
        $key = $this->getMemoryCacheKey($method, $arguments);
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $rows = $this->callRealDao($method, $arguments);
        $this->unserializes($rows);
        $this->cache[$key] = $rows;
        return $rows;
    }

    protected function search($method, $arguments)
    {
        $rows = $this->callRealDao($method, $arguments);
        $this->unserializes($rows);
        return $rows;
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

        $declares   = $this->dao->declares();
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
        $declares   = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!array_key_exists($key, $row)) {
                continue;
            }

            $row[$key] = $this->serializer->serialize($method, $row[$key]);
        }
    }
}
