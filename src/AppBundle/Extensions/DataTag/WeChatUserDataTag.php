<?php

namespace AppBundle\Extensions\DataTag;

use Topxia\Service\Common\ServiceKernel;

class WeChatUserDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取一个用户的关注/粉丝的数量.
     *
     * 可传入的参数：
     *   userId 必需 用户ID
     *
     * @param array $arguments 参数
     *
     * @return array 一个用户的关注/粉丝的数量
     */
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
