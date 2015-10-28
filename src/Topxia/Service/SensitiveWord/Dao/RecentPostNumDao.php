<?php

namespace Topxia\Service\SensitiveWord\Dao;

interface RecentPostNumDao
{
    public function getRecentPostNumByIp($ip);

}