<?php

namespace Topxia\Service\System\Dao;

interface IpBlacklistDao
{
    public function addIp($fields);

    public function getIp($id);

    public function getIpByIpAndType($ip, $type);

    public function findIpsByTypeAndExpiredTimeLessThan($type, $time, $start, $limit);

    public function increaseIpCounter($id, $diff);

    public function deleteIp($id);

}