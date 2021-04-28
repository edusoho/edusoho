<?php

namespace ApiBundle\Api\Resource\Template;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\SettingService;

class Template extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $userRoles = $this->getCurrentUser()->getRoles();

        $wechatNotificationSetting = $this->getSettingService()->get('wechat_notification');
        if (empty($wechatNotificationSetting['templates'])) {
            return '';
        }

        if ('MessageSubscribe' != $wechatNotificationSetting['notification_type'] || empty($wechatNotificationSetting['is_authorization'])) {
            return '';
        }

        $templateCodes = [];
        $subscribeTemplates = $this->container->get('extension.manager')->getMessageSubscribeTemplates();
        foreach ($wechatNotificationSetting['templates'] as $key => $template) {
            if (in_array($subscribeTemplates[$key]['role'], $userRoles) && !empty($template['templateId'])) {
                $templateCodes[] = $template['templateId'];
            }
        }

        return implode(',', $templateCodes);
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
