<?php

namespace OpenLivePlugin\Biz\Cash\Service\Impl;

use Biz\BaseService;
use OpenLivePlugin\Biz\Cash\Service\CashService;
use OpenLivePlugin\Biz\OpenLivePlatform\PlatformSdk;

class CashServiceImpl extends BaseService implements CashService
{
    public function getCashAccount()
    {
        return $this->getOpenLivePlatformSkd()->handelSdkResult($this->getOpenLivePlatformSkd()->getCashAccount());
    }

    /**
     * @return PlatformSdk
     */
    protected function getOpenLivePlatformSkd()
    {
        return $this->biz->offsetGet('open_live.plugin.open_live_platform');
    }
}