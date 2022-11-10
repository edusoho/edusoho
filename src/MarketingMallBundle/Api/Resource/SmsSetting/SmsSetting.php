<?php

namespace MarketingMallBundle\Api\Resource\SmsSetting;

use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;

class SmsSetting extends BaseResource
{
    public function search(ApiRequest $request)
    {
        $mySetting = $this->getSettingService()->get('cloud_sms', array());
        if (empty($mySetting)){
            return 0;
        }

        return $mySetting['sms_enabled'];
    }

    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}