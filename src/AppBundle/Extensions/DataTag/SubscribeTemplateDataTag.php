<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Common\CommonException;

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

        $wechatNotificationSetting = $this->getSettingService()->get('wechat_notification');
        if (empty($wechatNotificationSetting['templates'])) {
            return '';
        }

        if ('MessageSubscribe' != $wechatNotificationSetting['notification_type'] || empty($wechatNotificationSetting['is_authorization'])) {
            return '';
        }

        $templateCodes = [];
        global $kernel;
        $subscribeTemplates = $kernel->getContainer()->get('extension.manager')->getMessageSubscribeTemplates();
        foreach ($wechatNotificationSetting['templates'] as $key => $template) {
            if (in_array($subscribeTemplates[$key]['role'], $userRoles) && !empty($template['templateId'])) {
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
