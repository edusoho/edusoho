<?php

namespace MarketingMallBundle\Api\Resource\UserProtocolSetting;

use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;

class UserProtocolSetting extends BaseResource
{
    public function search(ApiRequest $request)
    {
        $auth = $this->getSettingService()->get('auth', []);

        return [
            'userTerms' => $auth['user_terms'] ?? 'closed',
            'userTermsBody' => $auth['user_terms_body'] ?? '',
            'privacyPolicy' => $auth['privacy_policy'] ?? 'closed',
            'privacyPolicyBody' => $auth['privacy_policy_body'] ?? '',
        ];
    }

    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
