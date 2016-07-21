<?php

namespace Topxia\Service\System\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\System\StatisticsService;

class StatisticsServiceImpl extends BaseService implements StatisticsService
{
    public function getOnlineCount($retentionTime)
    {
        if ($this->isRedisOpened()) {
            $onlines = $this->getRedis()->keys("session:online:*");
            return count($onlines);
        } else {
            return $this->getSessionDao()->getOnlineCount($retentionTime);
        }
    }

    public function getloginCount($retentionTime)
    {
        if ($this->isRedisOpened()) {
            $logins = $this->getRedis()->keys("session:logined:*");
            return count($logins);
        } else {
            return $this->getSessionDao()->getLoginCount($retentionTime);
        }
    }

    protected function isRedisOpened()
    {
        $redisSetting = $this->getSettingService()->get('redis', array());

        if (empty($redisSetting['opened']) || $redisSetting['opened'] == 0) {
            return false;
        }

        return true;
    }

    protected function getSessionDao()
    {
        return $this->createDao('System.SessionDao');
    }

    protected function getRedis($group = 'default')
    {
        return $this->getKernel()->getRedis($group);
    }

    protected function getSettingService()
    {
        return $this->getKernel()->createService('System.SettingService');
    }
}
