<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\StatisticsService;

class StatisticsServiceImpl extends BaseService implements StatisticsService
{
    public function countOnline($retentionTime)
    {
        return $this->getSessionDao()->countOnline($retentionTime);
    }

    public function countLogin($retentionTime)
    {
        return $this->getSessionDao()->countLogin($retentionTime);
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
