<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

class PromiseCacheStrategy extends CacheStrategy
{
    private $methodMap = array();

    public function wave($dao, $daoMethod, $arguments, $callback)
    {
        $table = $dao->table();
        $className = get_class($dao);
        if (in_array($daoMethod, array('update', 'delete'))) {
            $data       = call_user_func_array($callback, array($daoMethod, $arguments));
            if(empty($this->methodMap[$className])) {
                return;
            }

            $originData = $dao->get($arguments[0]);
            foreach ($this->methodMap[$className] as $method => $fields) {
                if ($this->isDataUpdated($fields, $originData, $data)) {
                    $args = array();
                    foreach ($fields as $field) {
                        $field = lcfirst($field);
                        $args[]   = $originData[$field];
                    }

                    $keys = $this->getKeys($method, $args);
                    $this->incrNamespaceVersion($dao, "{$table}:{$keys}");
                }
            }
        } else {
            $data = call_user_func_array($callback, array($daoMethod, $arguments));
            $this->incrNamespaceVersion($dao, $table);
        }
        return $data;
    }

    protected function isDataUpdated($fields, $originData, $data)
    {
        if(empty($originData) && !empty($data)) {
            return true;
        }

        foreach ($fields as $key => $field) {
            $field = lcfirst($field);
            if (!array_key_exists($field, $originData)) {
                continue;
            }

            if ($originData[$field] != $data[$field]) {
                return true;
            }
        }
        return false;
    }

    public function parseDao($dao)
    {
        $className = get_class($dao);
        $class   = new \ReflectionClass($className);
        $methods = $class->getMethods();
        foreach ($methods as $key => $method) {
            if ($method->isPublic()) {
                $methodName = $method->getName();
                $whiteList  = array('__construct', 'declares', 'db', 'table');
                if (in_array($methodName, $whiteList)) {
                    continue;
                }
                $this->parseMethod($className, $methodName);
            }
        }
    }

    protected function parseMethod($className, $methodName)
    {
        $prefix = $this->getPrefix($methodName, array('get', 'find'));
        if ($prefix && $prefix != $methodName) {
            $fields = str_replace("{$prefix}By", '', $methodName);
            $fields = explode('And', $fields);

            if (!isset($this->methodMap[$className])) {
                $this->methodMap[$className] = array();
            }

            if(empty($this->methodMap[$className][$methodName])){
                $this->methodMap[$className][$methodName] = $fields;
            }
        }
    }
    
    protected function generateKey($dao, $method, $args)
    {
        $table = $dao->table();
        if ($method == 'get') {

            return "{$table}:{$this->getVersionByNamespace($dao, $table)}:id:{$args[0]}";
        }

        $keys    = $this->getKeys($method, $args);
        $version = $this->getVersionByNamespace($dao, "{$table}:{$keys}");

        return "{$table}:version:{$this->getVersionByNamespace($dao, $table)}:{$keys}:version:{$version}";
    }

    protected function getKeys($method, $args)
    {
        $fileds = $this->parseFields($method);
        $keys   = '';
        foreach ($fileds as $key => $value) {
            if (!empty($keys)) {
                $keys = "{$keys}:";
            }
            $keys = $keys.$value.':'.$args[$key];
        }

        return $keys;
    }
}
