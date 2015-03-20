<?php

namespace Topxia\Service\System\Dao;

interface CacheDao
{

    public function findCachesByNames(array $names);

    public function addCache($cache);

    public function deleteCacheByName($name);

    public function deleteAllCache();

}