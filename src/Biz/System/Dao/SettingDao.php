<?php

namespace Biz\System\Dao;

interface SettingDao
{
    public function findAll();

    public function deleteByName($name);

    public function deleteByNamespaceAndName($namespace, $name);
}
