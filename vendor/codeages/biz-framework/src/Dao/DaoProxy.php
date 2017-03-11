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
        if (strpos($method, 'get') === 0) {
            $row = $this->callRealDao($method, $arguments);

            return $this->unserialize($row);
        }

        if ((strpos($method, 'find') === 0) || (strpos($method, 'search') === 0)) {
            $rows = $this->callRealDao($method, $arguments);

            return $this->unserializes($rows);
        }

        $declares = $this->dao->declares();
        if (strpos($method, 'create') === 0) {
            if (isset($declares['timestamps'][0])) {
                $arguments[0][$declares['timestamps'][0]] = time();
            }

            if (isset($declares['timestamps'][1])) {
                $arguments[0][$declares['timestamps'][1]] = time();
            }

            $arguments[0] = $this->serialize($arguments[0]);
            $row = $this->callRealDao($method, $arguments);

            return $this->unserialize($row);
        }

        if (strpos($method, 'update') === 0) {
            if (isset($declares['timestamps'][1])) {
                $arguments[1][$declares['timestamps'][1]] = time();
            }
            $arguments[1] = $this->serialize($arguments[1]);

            $row = $this->callRealDao($method, $arguments);

            return $this->unserialize($row);
        }

        return $this->callRealDao($method, $arguments);
    }

    private function callRealDao($method, $arguments)
    {
        return call_user_func_array(array($this->dao, $method), $arguments);
    }

    private function unserialize(&$row)
    {
        if (empty($row)) {
            return $row;
        }

        $declares = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!isset($row[$key])) {
                continue;
            }
            $row[$key] = $this->serializer->unserialize($method, $row[$key]);
        }

        return $row;
    }

    private function unserializes(array &$rows)
    {
        if (empty($rows)) {
            return $rows;
        }

        foreach ($rows as &$row) {
            $this->unserialize($row);
        }

        return $rows;
    }

    private function serialize(&$row)
    {
        $declares = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!isset($row[$key])) {
                continue;
            }

            $row[$key] = $this->serializer->serialize($method, $row[$key]);
        }

        return $row;
    }
}
