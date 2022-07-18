<?php

namespace ApiBundle\Api\Resource\MultiClassSetting;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Common\CommonException;
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
        if (!$this->checkSettings($request)) {
            throw CommonException::ERROR_PARAMETER();
        }

        $multiClassSetting = $this->getSettingService()->get('multi_class', []);
        $multiClassSetting = empty($multiClassSetting) ? $this->getMultiClassDefaultSettings() : $multiClassSetting;
        $updateSettings = $request->request->all();
        $settings = array_merge($multiClassSetting, $updateSettings);
        $this->getSettingService()->set('multi_class', $settings);

        return $this->getSettingService()->get('multi_class');
    }

    private function checkSettings(ApiRequest $request)
    {
        $groupNumberLimit = $request->request->get('group_number_limit', '');
        if ($groupNumberLimit && !is_numeric($groupNumberLimit) || false !== strpos($groupNumberLimit, '.')) {
            return false;
        }

        $assistantGroupLimit = $request->request->get('assistant_group_limit', '');
        if ($assistantGroupLimit && !is_numeric($assistantGroupLimit) || false !== strpos($assistantGroupLimit, '.')) {
            return false;
        }

        $assistantServiceLimit = $request->request->get('assistant_service_limit', '');
        if ($assistantServiceLimit && !is_numeric($assistantServiceLimit) || false !== strpos($assistantServiceLimit, '.')) {
            return false;
        }

        $reviewTimeLimit = $request->request->get('review_time_limit', '');
        if ($reviewTimeLimit && !is_numeric($reviewTimeLimit) || false !== strpos($reviewTimeLimit, '.')) {
            return false;
        }

        return true;
    }

    private function getMultiClassDefaultSettings()
    {
        return [
            'group_number_limit' => '',
            'assistant_group_limit' => '',
            'assistant_service_limit' => '',
            'review_time_limit' => '0',
        ];
    }
}
