<?php

namespace Codeages\Biz\Framework\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

class DoubleCacheStrategy extends AbstractCacheStrategy implements CacheStrategy
{
    private $first;

    private $second;

    public function setStrategies($first, $second)
    {
        $this->first = $first;
        $this->second = $second;
    }

    public function beforeGet(GeneralDaoInterface $dao, $method, $arguments)
    {
        $cache = $this->first->beforeGet($dao, $method, $arguments);
        if ($cache && $cache !== false) {
            return $cache;
        }

        return $this->second->beforeGet($dao, $method, $arguments);
    }

    public function afterGet(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->first->afterGet($dao, $method, $arguments, $row);
        $this->second->afterGet($dao, $method, $arguments, $row);
    }

    public function beforeFind(GeneralDaoInterface $dao, $method, $arguments)
    {
        $cache = $this->first->beforeFind($dao, $method, $arguments);
        if ($cache && $cache !== false) {
            return $cache;
        }

        return $this->second->beforeFind($dao, $method, $arguments);
    }

    public function afterFind(GeneralDaoInterface $dao, $method, $arguments, array $rows)
    {
        $this->first->afterGet($dao, $method, $arguments, $rows);
        $this->second->afterGet($dao, $method, $arguments, $rows);
    }

    public function beforeSearch(GeneralDaoInterface $dao, $method, $arguments)
    {
        $cache = $this->first->beforeSearch($dao, $method, $arguments);
        if ($cache && $cache !== false) {
            return $cache;
        }

        return $this->second->beforeSearch($dao, $method, $arguments);
    }

    public function afterSearch(GeneralDaoInterface $dao, $method, $arguments, array $rows)
    {
        $this->first->afterSearch($dao, $method, $arguments, $rows);
        $this->second->afterSearch($dao, $method, $arguments, $rows);
    }

    public function beforeCount(GeneralDaoInterface $dao, $method, $arguments)
    {
        $cache = $this->first->beforeCount($dao, $method, $arguments);
        if ($cache && $cache !== false) {
            return $cache;
        }

        return $this->second->beforeCount($dao, $method, $arguments);
    }

    public function afterCount(GeneralDaoInterface $dao, $method, $arguments, $count)
    {
        $this->first->afterCount($dao, $method, $arguments, $count);
        $this->second->afterCount($dao, $method, $arguments, $count);
    }

    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->first->afterCreate($dao, $method, $arguments, $row);
        $this->second->afterCreate($dao, $method, $arguments, $row);
    }

    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        $this->first->afterUpdate($dao, $method, $arguments, $row);
        $this->second->afterUpdate($dao, $method, $arguments, $row);
    }

    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected)
    {
        $this->first->afterWave($dao, $method, $arguments, $affected);
        $this->second->afterWave($dao, $method, $arguments, $affected);
    }

    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments)
    {
        $this->first->afterDelete($dao, $method, $arguments);
        $this->second->afterDelete($dao, $method, $arguments);
    }
}
