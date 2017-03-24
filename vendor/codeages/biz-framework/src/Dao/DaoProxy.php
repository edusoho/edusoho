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
            $daoMethod = 'get';
        }

        if ((strpos($method, 'find') === 0) || (strpos($method, 'search') === 0)) {
            $daoMethod = 'find';
        }

        if (strpos($method, 'create') === 0) {
            $daoMethod = 'create';
        }

        if (strpos($method, 'update') === 0) {
            $daoMethod = 'update';
        }

        if (null !== $daoMethod) {
            return $this->$daoMethod($method, $arguments);
        }

        return $this->callRealDao($method, $arguments);
    }

    private function get($method, $arguments)
    {
        $row = $this->callRealDao($method, $arguments);
        $this->unserialize($row);

        return $row;
    }

    private function update($method, $arguments)
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
        $this->unserialize($row);

        return $row;
    }

    private function create($method, $arguments)
    {
        $declares = $this->dao->declares();
        if (isset($declares['timestamps'][0])) {
            $arguments[0][$declares['timestamps'][0]] = time();
        }

        if (isset($declares['timestamps'][1])) {
            $arguments[0][$declares['timestamps'][1]] = time();
        }

        $this->serialize($arguments[0]);
        $row = $this->callRealDao($method, $arguments);
        $this->unserialize($row);

        return $row;
    }

    private function find($method, $arguments)
    {
        $rows = $this->callRealDao($method, $arguments);
        $this->unserializes($rows);

        return $rows;
    }

    private function callRealDao($method, $arguments)
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
