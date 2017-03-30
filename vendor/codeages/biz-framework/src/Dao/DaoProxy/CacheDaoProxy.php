<?php

namespace Codeages\Biz\Framework\Dao\DaoProxy;

class CacheDaoProxy extends DaoProxy
{
    protected static $cacheDelegate;

    public function setDao($dao)
    {
        $this->dao = $dao;

        if ($this->hasCacheStrategy($this->dao)) {
            if (empty(self::$cacheDelegate)) {
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
        if (!$hasCacheStrategy) {
            return parent::__call($method, $arguments);
        }

        if ($this->shouldCacheProcess($method)) {
            return self::$cacheDelegate->proccess($this->dao, $method, $arguments, function ($method, $arguments) {
                return parent::__call($method, $arguments);
            });
        } else {
            return parent::__call($method, $arguments);
        }
    }

    protected function shouldCacheProcess($method)
    {
        return $this->getPrefix($method, array('get', 'find', 'create', 'update', 'delete', 'wave'));
    }
}
