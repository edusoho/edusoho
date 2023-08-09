<?php

namespace MarketingMallBundle\Api\Resource\UserPasswordLevel;

use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;

class UserPasswordLevel extends BaseResource
{
    public function search(ApiRequest $request)
    {
        $auth = $this->getSettingService()->get('auth', []);

        return [
            'passwordLevel' => $auth['password_level'] ?? 'low',
        ];
    }

    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
