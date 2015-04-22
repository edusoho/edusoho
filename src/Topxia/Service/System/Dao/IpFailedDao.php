<?php

namespace Topxia\Service\System\Dao;

interface IpFailedDao
{
    public function addIp($fields);

    public function getIp($id);

    public function getIpByIp($ip);

    public function findIpsByExpiredTimeLessThan($time, $start, $limit);

    public function increaseIpCounter($id, $diff);

    public function deleteIp($id);

}