<?php

namespace MarketingMallBundle\Api\Resource\SmsCloudSetting;

use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;

class SmsCloudSetting extends BaseResource
{
    public function search(ApiRequest $request)
    {
        $mySetting = $this->getSettingService()->get('cloud_sms', []);
        if (empty($mySetting)) {
            return 0;
        }

        if (isset($mySetting['sms_shipping_notify']) && 'on' == $mySetting['sms_shipping_notify']) {
            return ['isShippingNotifyEnabled' => true];
        }

        return ['isShippingNotifyEnabled' => false];
    }

    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
