<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\IpBlacklistService;

class IpBlacklistServiceImpl extends BaseService implements IpBlacklistService
{
    public function increaseIpFailedCount($ip)
    {
        $setting = $this->getSettingService()->get('login_bind', array());
        $setting = array_merge(array('temporary_lock_minutes' => 20), $setting);

        $existIp = $this->getIpBlacklistDao()->getByIpAndType($ip, 'failed');
        if (empty($existIp)) {
            $ip = array(
                'ip' => $ip,
                'type' => 'failed',
                'counter' => 1,
                'expiredTime' => time() + ($setting['temporary_lock_minutes'] * 60),
                'createdTime' => time(),
            );
            $ip = $this->getIpBlacklistDao()->create($ip);

            return $ip['counter'];
        }

        if ($this->isIpExpired($existIp)) {
            $this->getIpBlacklistDao()->delete($existIp['id']);

            $ip = array(
                'ip' => $ip,
                'type' => 'failed',
                'counter' => 1,
                'expiredTime' => time() + ($setting['temporary_lock_minutes'] * 60),
                'createdTime' => time(),
            );
            $ip = $this->getIpBlacklistDao()->create($ip);

            return $ip['counter'];
        }

        $this->getIpBlacklistDao()->wave(array($existIp['id']), array('counter' => 1));

        return $existIp['counter'] + 1;
    }

    public function getIpFailedCount($ip)
    {
        $ip = $this->getIpBlacklistDao()->getByIpAndType($ip, 'failed');
        if (empty($ip)) {
            return 0;
        }

        if ($this->isIpExpired($ip)) {
            $this->getIpBlacklistDao()->delete($ip['id']);

            return 0;
        }

        return $ip['counter'];
    }

    public function clearFailedIp($ip)
    {
        $ip = $this->getIpBlacklistDao()->getByIpAndType($ip, 'failed');
        if (empty($ip)) {
            return;
        }

        $this->getIpBlacklistDao()->delete($ip['id']);
    }

    protected function isIpExpired($ip)
    {
        return $ip['expiredTime'] < time();
    }

    protected function getIpBlacklistDao()
    {
        return $this->createDao('System:IpBlacklistDao');
    }

    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
