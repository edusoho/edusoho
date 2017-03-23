<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

class TableCacheStrategy extends CacheStrategy
{
    public function wave($dao, $method, $arguments, $callback)
    {
        $data = call_user_func_array($callback, array($method, $arguments));
        $table = $dao->table();
        $this->incrNamespaceVersion($dao, $table);
        return $data;
    }

    protected function generateKey($dao, $method, $args)
    {   
        $table = $dao->table();
        if ($method == 'get') {
            return "{$table}:{$this->getVersionByNamespace($dao, $table)}:id:{$args[0]}";
        }

        $fileds = $this->parseFields($method);
        $keys   = '';
        foreach ($fileds as $key => $value) {
            if (!empty($keys)) {
                $keys = "{$keys}:";
            }
            $keys = $keys.$value.':'.$args[$key];
        }

        return "{$table}:{$this->getVersionByNamespace($dao, $table)}:{$keys}";
    }
}
