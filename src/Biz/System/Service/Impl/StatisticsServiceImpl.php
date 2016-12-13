<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\StatisticsService;

class StatisticsServiceImpl extends BaseService implements StatisticsService
{
    public function getOnlineCount($retentionTime)
    {
        return $this->getSessionDao()->getOnlineCount($retentionTime);
    }

    public function getloginCount($retentionTime)
    {
        return $this->getSessionDao()->getLoginCount($retentionTime);
    }

    protected function getSessionDao()
    {
        return $this->createDao('System:SessionDao');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
