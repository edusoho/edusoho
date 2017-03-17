<?php

namespace Codeages\Biz\Framework\Dao;

class DaoProxy implements DaoProxyInterface
{
    protected $dao;

    protected $serializer;

    public function __construct(DaoInterface $dao, SerializerInterface $serializer)
    {
        $this->dao = $dao;
        $this->serializer = $serializer;
    }

    public function __call($method, $arguments)
    {
        $daoMethod = null;
        if (strpos($method, 'get') === 0) {
            $daoMethod = '_get';
        }

        if ((strpos($method, 'find') === 0) || (strpos($method, 'search') === 0)) {
            $daoMethod = '_find';
        }

        if (strpos($method, 'create') === 0) {
            $daoMethod = '_create';
        }

        if (strpos($method, 'update') === 0) {
            $daoMethod = '_update';
        }

        if (null !== $daoMethod) {
            return $this->$daoMethod($method, $arguments);
        }

        return $this->_callRealDao($method, $arguments);
    }

    private function _get($method, $arguments)
    {
        $row = $this->_callRealDao($method, $arguments);
        $this->unserialize($row);

        return $row;
    }

    private function _update($method, $arguments)
    {
        $declares = $this->dao->declares();
        if (isset($declares['timestamps'][1])) {
            $arguments[1][$declares['timestamps'][1]] = time();
        }

        if (is_array($arguments[1])) {
            $this->serialize($arguments[1]);
        }

        $row = $this->_callRealDao($method, $arguments);
        $this->unserialize($row);

        return $row;
    }

    private function _create($method, $arguments)
    {
        $declares = $this->dao->declares();
        if (isset($declares['timestamps'][0])) {
            $arguments[0][$declares['timestamps'][0]] = time();
        }

        if (isset($declares['timestamps'][1])) {
            $arguments[0][$declares['timestamps'][1]] = time();
        }

        $this->serialize($arguments[0]);
        $row = $this->_callRealDao($method, $arguments);
        $this->unserialize($row);

        return $row;
    }

    private function _find($method, $arguments)
    {
        $rows = $this->_callRealDao($method, $arguments);
        $this->unserializes($rows);

        return $rows;
    }

    private function _callRealDao($method, $arguments)
    {
        return call_user_func_array(array($this->dao, $method), $arguments);
    }

    private function unserialize(&$row)
    {
        if (!is_array($row) || empty($row)) {
            return;
        }

        $declares = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (array_key_exists($key, $row)) {
                $row[$key] = $this->serializer->unserialize($method, $row[$key]);
            }
        }
    }

    private function unserializes(array &$rows)
    {
        if (empty($rows)) {
            return;
        }

        foreach ($rows as &$row) {
            $this->unserialize($row);
        }
    }

    private function serialize(array &$row)
    {
        $declares = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (array_key_exists($key, $row)) {
                $row[$key] = $this->serializer->serialize($method, $row[$key]);
            }
        }
    }
}
