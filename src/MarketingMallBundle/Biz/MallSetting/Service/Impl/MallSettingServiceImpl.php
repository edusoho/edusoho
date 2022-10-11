<?php

namespace MarketingMallBundle\Biz\MallSetting\Service\Impl;

use Biz\BaseService;
use MarketingMallBundle\Biz\MallSetting\Service\MallSettingService;

class MallSettingServiceImpl extends BaseService implements MallSettingService
{
    public function isShowMall()
    {
        return $this->getSetting('cloud_status.accessCloud', false) && !$this->getSetting('developer.without_network', $default = false);
    }

    protected function getSetting($name, $default = null)
    {
        return $this->biz->service('System:SettingService')->node($name, $default);
    }
}