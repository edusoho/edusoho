<?php

namespace AppBundle\Extensions\DataTag;

use Topxia\Service\Common\ServiceKernel;

class WeChatUserDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $userId = $arguments['userId'];

        $wechatSetting = $this->getSettingService()->get('wechat', array());
        if (empty($wechatSetting['wechat_notification_enabled'])) {
            return array();
        }

        return $this->getWeChatService()->getOfficialWeChatUserByUserId($userId) ?: array();
    }

    private function getWeChatService()
    {
        return ServiceKernel::instance()->createService('WeChat:WeChatService');
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }
}
