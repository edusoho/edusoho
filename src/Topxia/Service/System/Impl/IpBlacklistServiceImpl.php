<?php
namespace Topxia\Service\System\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\System\IpBlacklistService;

class IpBlacklistServiceImpl extends BaseService implements IpBlacklistService
{

    public function increaseIpFailedCount($ip)
    {
        $ip = $this->getIpBlacklistDao()->getIpByIpAndType($ip, 'failed');
        if (empty($ip)) {
            $ip = array(
                'ip' => $ip,
                'type' => 'failed',
                'counter' => 1,
                'expiredTime' => time() + self::FAILED_DURATION,
                'createdTime' => time(),
            );
           $ip = $this->getIpBlacklistDao()->addIp($ip);

           return $ip['counter'];
        }

        if ($this->isIpExpired($ip)) {
            $this->getIpBlacklistDao()->deleteIp($ip['id']);

            $ip = array(
                'ip' => $ip,
                'type' => 'failed',
                'counter' => 1,
                'expiredTime' => time() + self::FAILED_DURATION,
                'createdTime' => time(),
            );
           $ip = $this->getIpBlacklistDao()->addIp($ip);

           return $ip['counter'];
        }

        $this->getIpBlacklistDao()->increaseIpCounter($ip['id'], 1);

        return $ip['counter'] + 1;
    }

    public function getIpFailedCount($ip)
    {
        $ip = $this->getIpBlacklistDao()->getIpByIpAndType($ip, 'failed');
        if (empty($ip)) {
            return 0;
        }

        if ($this->isIpExpired($ip)) {
            $this->getIpBlacklistDao()->deleteIp($ip['id']);
            return 0;
        }

        return $ip['counter'];
    }

    public function clearFailedIp($ip)
    {
        $ip = $this->getIpBlacklistDao()->getIpByIpAndType($ip, 'failed');
        if (empty($ip)) {
            return ;
        }

        $this->getIpBlacklistDao()->deleteIp($ip['id']);
    }

    protected function isIpExpired($ip)
    {
        return $ip['expiredTime'] < time();
    }

    protected function getIpBlacklistDao()
    {
        return $this->createDao('System.IpBlacklistDao');
    }

}