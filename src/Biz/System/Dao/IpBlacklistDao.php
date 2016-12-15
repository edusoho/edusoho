<?php

namespace Biz\System\Dao;

interface IpBlacklistDao
{
    public function getByIpAndType($ip, $type);
}
