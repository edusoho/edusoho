<?php

namespace AppBundle\Extensions\DataTag;

class SubscribeTemplateDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取微信模板.
     *
     * @param array $arguments 参数
     *
     * @return string
     */
    public function getData(array $arguments)
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
        global $kernel;
        $subscribeTemplates = $kernel->getContainer()->get('extension.manager')->getMessageSubscribeTemplates();
        foreach ($wechatNotificationSetting['templates'] as $key => $template) {
            if ($subscribeTemplates[$key]['role'] == $userRole && !empty($template['templateId'])) {
                $templateCodes[] = $template['templateId'];
            }
        }

        return implode(',', $templateCodes);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
    }
}
