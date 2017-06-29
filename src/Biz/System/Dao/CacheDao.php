<?php

namespace Biz\System\Dao;

interface CacheDao
{
    public function getByName($name);

    public function findByNames(array $names);

    public function updateByName($name, $cache);

    public function deleteByName($name);

    public function deleteAll();
}
