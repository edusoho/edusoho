<?php

namespace ApiBundle\Api\Resource\MultiClassSetting;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\System\Service\SettingService;

class MultiClassSetting extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $default = $this->getMultiClassDefaultSettings();
        $multiClassSetting = $this->getSettingService()->get('multi_class', []);

        return array_merge($default, $multiClassSetting);
    }

    public function add(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2_education')) {
            throw new AccessDeniedException();
        }

        $multiClassSetting = $this->getSettingService()->get('multi_class', []);
        $multiClassSetting = empty($multiClassSetting) ? $this->getMultiClassDefaultSettings() : $multiClassSetting;
        $updateSettings = $request->request->all();
        $settings = array_merge($multiClassSetting, $updateSettings);
        $this->getSettingService()->set('multi_class', $settings);

        return $this->getSettingService()->get('multi_class');
    }

    private function getMultiClassDefaultSettings()
    {
        return [
            'group_number_limit' => '20',
            'assistant_service_limit' => '200',
            'review_time_limit' => '24',
        ];
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
