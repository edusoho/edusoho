<?php

namespace Codeages\Biz\Framework\Dao\DaoProxy;

class CacheDelegate
{
    private $daoStrategyMap = array();
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function parseDao($dao)
    {
        $daoClass = get_class($dao);
        if(empty($this->daoStrategyMap[$daoClass])) {
            $strategy = $this->getCacheStrategy($dao);
            $strategy->parseDao($dao);
            $this->daoStrategyMap[$daoClass] = $strategy;
        }
    }

    private function getCacheStrategy($dao)
    {
        $declares = $dao->declares();
        return $this->container["cache.dao.strategy.{$declares['cache']}"];
    }

    public function proccess($dao, $daoMethod, $arguments, $callback)
    {
        $prefix = $this->getPrefix($daoMethod, array('get', 'find', 'create', 'update', 'delete', 'wave'));
        if (empty($prefix)) {
            throw new \InvalidArgumentException('daoMethod is invalid. ');
        }

        if (in_array($prefix, array('get', 'find'))) {
            return $this->fetchCache($dao, $daoMethod, $arguments, $callback);
        } else {
            $strategy = $this->daoStrategyMap[get_class($dao)];
            return $strategy->wave($dao, $daoMethod, $arguments, $callback);
        }
    }

    protected function fetchCache($dao, $daoMethod, $arguments, $callback)
    {
        $strategy = $this->daoStrategyMap[get_class($dao)];
        $data = $strategy->get($dao, $daoMethod, $arguments);

        if ($data !== false) {
            return $data;
        }

        $data = call_user_func_array($callback, array($daoMethod, $arguments));

        $strategy->set($dao, $daoMethod, $arguments, $data);

        return $data;
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
