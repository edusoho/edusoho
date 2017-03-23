<?php

namespace Codeages\Biz\Framework\Dao\DaoProxy;

class CacheDaoProxy extends DaoProxy
{
    protected static $cacheDelegate;

    public function setDao($dao)
    {
        $this->dao = $dao;

        if ($this->hasCacheStrategy($this->dao)) {
            if(empty(self::$cacheDelegate)) {
                self::$cacheDelegate = $this->container['cache.dao.delegate'];
            }
            self::$cacheDelegate->parseDao($dao);
        }
    }

    protected function hasCacheStrategy()
    {
        $declares = $this->dao->declares();
        return !empty($declares['cache']);
    }

    public function __call($method, $arguments)
    {

        $hasCacheStrategy = $this->hasCacheStrategy($this->dao);
        if(!$hasCacheStrategy) {
            return parent::__call($method, $arguments);
        }

        $that       = $this;
        $daoProxyMethod = $this->getDaoProxyMethod($method);

        if ($daoProxyMethod) {
            return self::$cacheDelegate->proccess($this->dao, $method, $arguments, function ($method, $arguments) use ($that, $daoProxyMethod) {
                return $that->$daoProxyMethod($method, $arguments);
            });
        } elseif ($this->getPrefix($method, array('search'))) {
            return $this->_search($method, $arguments);
        } else {
            return $this->_callRealDao($method, $arguments);
        }
    }

    protected function getDaoProxyMethod($method)
    {
        $prefix = $this->getPrefix($method, array('get', 'find', 'create', 'update', 'delete'));
        if ($prefix) {
            return "_{$prefix}";
        }

        if ($this->getPrefix($method, array('wave'))) {
            return "_callRealDao";
        }
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
}
