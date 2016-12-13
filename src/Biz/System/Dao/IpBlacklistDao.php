<?php

namespace Biz\System\Dao;

interface IpBlacklistDao
{
    public function getByIpAndType($ip, $type);

    public function findByTypeAndExpiredTimeLessThan($type, $time, $start, $limit);

    public function increaseIpCounter($id, $diff);
}
