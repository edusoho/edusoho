<?php

namespace Codeages\Biz\Framework\Dao;

class DaoProxy
{
    protected $container;

    protected $dao;

    public function __construct($container, $dao)
    {
        $this->container = $container;
        $this->dao = $dao;
    }

    public function __call($method, $arguments)
    {
        if (strpos($method, 'get') === 0) {
            $row = $this->_callRealDao($method, $arguments);

            return $this->_unserialize($row);
        }

        if ((strpos($method, 'find') === 0) or (strpos($method, 'search') === 0)) {
            $rows = $this->_callRealDao($method, $arguments);

            return $this->_unserializes($rows);
        }

        $declares = $this->dao->declares();
        if (strpos($method, 'create') === 0) {
            if (isset($declares['timestamps'][0])) {
                $arguments[0][$declares['timestamps'][0]] = time();
            }

            if (isset($declares['timestamps'][1])) {
                $arguments[0][$declares['timestamps'][1]] = time();
            }

            $arguments[0] = $this->_serialize($arguments[0]);
            $row = $this->_callRealDao($method, $arguments);

            return $this->_unserialize($row);
        }

        if (strpos($method, 'update') === 0) {
            if (isset($declares['timestamps'][1])) {
                $arguments[1][$declares['timestamps'][1]] = time();
            }
            $arguments[1] = $this->_serialize($arguments[1]);

            $row = $this->_callRealDao($method, $arguments);

            return $this->_unserialize($row);
        }

        return $this->_callRealDao($method, $arguments);
    }

    private function _callRealDao($method, $arguments)
    {
        return call_user_func_array(array($this->dao, $method), $arguments);
    }

    private function _unserialize(&$row)
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
            $method = "_{$method}Unserialize";
            $row[$key] = $this->$method($row[$key]);
        }

        return $row;
    }

    private function _unserializes(array &$rows)
    {
        foreach ($rows as &$row) {
            $this->_unserialize($row);
        }

        return $rows;
    }

    private function _serialize(&$row)
    {
        $declares = $this->dao->declares();
        $serializes = empty($declares['serializes']) ? array() : $declares['serializes'];

        foreach ($serializes as $key => $method) {
            if (!isset($row[$key])) {
                continue;
            }
            $method = "_{$method}Serialize";
            $row[$key] = $this->$method($row[$key]);
        }

        return $row;
    }

    private function _jsonSerialize($value)
    {
        if (empty($value)) {
            return '';
        }

        return json_encode($value);
    }

    private function _jsonUnserialize($value)
    {
        if (empty($value)) {
            return array();
        }

        return json_decode($value, true);
    }

    private function _delimiterSerialize($value)
    {
        if (empty($value)) {
            return '';
        }

        return '|'.implode('|', $value).'|';
    }

    private function _delimiterUnserialize($value)
    {
        if (empty($value)) {
            return array();
        }

        return explode('|', trim($value, '|'));
    }
}
