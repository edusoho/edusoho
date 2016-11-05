<?php

namespace Topxia\Service\System\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\System\Dao\Impl\SessionDaoImpl;
use Topxia\Service\System\StatisticsService;

class StatisticsServiceImpl extends BaseService implements StatisticsService
{
    public function getOnlineCount($retentionTime)
    {
        if ($this->isRedisOpened()) {
            $currentTime = time();
            $start       = $currentTime - 15 * 60;
            return $this->getRedis()->zCount("session:online", $start, $currentTime);
        } else {
            return $this->getSessionDao()->getOnlineCount($retentionTime);
        }
    }

    public function getloginCount($retentionTime)
    {
        if ($this->isRedisOpened()) {
            $currentTime = time();
            $start       = $currentTime - 15 * 60;
            return $this->getRedis()->zCount("session:logined", $start, $currentTime);
        } else {
            return $this->getSessionDao()->getLoginCount($retentionTime);
        }
    }

    protected function isRedisOpened()
    {
        $redisPath = $this->getKernel()->getParameter('kernel.root_dir').'/data/redis.php';

        if (file_exists($redisPath)) {
            return true;
        }

        return false;
    }

    /**
     * @return SessionDaoImpl
     */
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
