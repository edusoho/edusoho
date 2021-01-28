<?php

namespace AppBundle\Extensions\DataTag;

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
        return $this->createService('WeChat:WeChatService');
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
