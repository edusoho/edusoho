<?php

namespace Biz\Role\Dao;

interface RoleDao
{
    public function getByCode($code);

    public function getByName($name);

    public function findByCodes($codes);
}
