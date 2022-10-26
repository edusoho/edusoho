<?php

namespace MarketingMallBundle\Biz\MallSetting\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\Service\EduCloudService;
use MarketingMallBundle\Biz\MallSetting\Service\MallSettingService;

class MallSettingServiceImpl extends BaseService implements MallSettingService
{
    public function isShowMall()
    {
        return $this->getSetting('cloud_status.accessCloud', false)
            && !$this->getSetting('developer.without_network', false)
            && $this->getEduCloudService()->isSaaS();
    }

    protected function getSetting($name, $default = null)
    {
        return $this->createService('System:SettingService')->node($name, $default);
    }

    /**
     * @return EduCloudService
     */
    protected function getEduCloudService()
    {
        return $this->createService('CloudPlatform:EduCloudService');
    }
}