<?php

namespace Biz\System\Service;

interface IpBlacklistService
{
    /**
     * 列入失败IP黑名单的持续时长
     */
    const FAILED_DURATION = 3600;

    /**
     * 累计IP的非法访问次数。例：密码输错次数.
     *
     * @param string $ip IP
     */
    public function increaseIpFailedCount($ip);

    public function getIpFailedCount($ip);

    public function clearFailedIp($ip);
}
