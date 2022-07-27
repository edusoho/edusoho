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
        $userRole = in_array('ROLE_TEACHER', $userRoles) ? 'ROLE_TEACHER' : 'ROLE_USER';

        $wechatNotificationSetting = $this->getSettingService()->get('wechat_notification');
        if (empty($wechatNotificationSetting['templates'])) {
            return '';
        }

        if ('messageSubscribe' != $wechatNotificationSetting['notification_type'] || empty($wechatNotificationSetting['is_authorization'])) {
            return '';
        }

        $templateCodes = [];
        $subscribeTemplates = $this->container->get('extension.manager')->getMessageSubscribeTemplates();
        foreach ($wechatNotificationSetting['templates'] as $key => $template) {
            if ($subscribeTemplates[$key]['role'] == $userRole && !empty($template['templateId'])) {
                $templateCodes[] = $template['templateId'];
            }
        }

        return implode(',', $templateCodes);
    }
}
