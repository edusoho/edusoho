<?php

namespace ApiBundle\Api\Resource\Template;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\SettingService;

class Template extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=true)
     */
    public function get(ApiRequest $request)
    {
        $role = $this->getCurrentUser()->getRoles();

        return $this->getMessageSubscribeTemplateCodesByUserRole($role);
    }

    private function getMessageSubscribeTemplateCodesByUserRole($userRole)
    {
        $role = in_array('ROLE_TEACHER', $userRole) ? 'ROLE_TEACHER' : 'ROLE_USER';
        $wechatNotificationSetting = $this->getSettingService()->get('wechat_notification');

        if ('MessageSubscribe' != $wechatNotificationSetting['notification_type'] || empty($wechatNotificationSetting['is_authorization'])) {
            return '';
        }
        $templateCodes = [];
        foreach ($wechatNotificationSetting['templates'] as $template) {
            if ($template['role'] == $role) {
                $templateCodes[] = $template['id'];
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
