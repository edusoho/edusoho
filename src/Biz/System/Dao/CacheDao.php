<?php

namespace Biz\System\Dao;

interface CacheDao
{
    public function findCachesByNames(array $names);

    public function addCache($cache);

    public function updateCache($name, $cache);

    public function deleteCacheByName($name);

    public function deleteAllCache();

}
