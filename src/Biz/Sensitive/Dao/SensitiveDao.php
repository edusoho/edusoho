<?php

namespace Biz\Sensitive\Dao;

interface SensitiveDao
{
    public function getByName($name);

    public function findAllKeywords();

    public function findByState($state);
}
